<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    // Estados de pedido
    public const STATUS_PENDING    = 'pending';
    public const STATUS_CONFIRMED  = 'confirmed';
    public const STATUS_PREPARING  = 'preparing';
    public const STATUS_READY      = 'ready';
    public const STATUS_ON_THE_WAY = 'on_the_way';
    public const STATUS_DELIVERED  = 'delivered';
    // Estados activos para microturnos (cuentan para capacidad)
    public const ACTIVE_STATUSES = ['confirmed', 'preparing', 'ready', 'on_the_way'];

    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'microturno_sort_order',
        'status',
        'confirmed_at',
        'preparing_at',
        'ready_at',
        'out_for_delivery_at',
        'payment_method',
        'payment_status',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'coupon_id',
        'total_amount',
        'contact_name',
        'contact_phone',
        'notes',
        'estimated_delivery_time',
        'delivered_at',
        'last_modified_at',
        'last_modified_by',
        'change_log',
        'review_prompt_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'preparing_at' => 'datetime',
            'ready_at' => 'datetime',
            'out_for_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
            'review_prompt_sent_at' => 'datetime',
            'last_modified_at' => 'datetime',
            'change_log' => 'array',
        ];
    }

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Método para obtener el microturno dinámico correspondiente
    public function getMicroturnoAttribute()
    {
        try {
            // Si tenemos sort_order almacenado, usarlo
            if ($this->microturno_sort_order) {
                $fecha = $this->created_at->format('Y-m-d');
                $microturnos = \App\Models\DynamicMicroturno::generarParaFecha($fecha);
                return $microturnos->first(function ($m) {
                    return $m->getSortOrderAttribute() == $this->microturno_sort_order;
                });
            }

            // Si no, calcular dinámicamente basándose en la hora de creación
            return \App\Models\DynamicMicroturno::encontrarParaPedido($this);
        } catch (\Exception $e) {
            // Si hay algún error, retornar null
            return null;
        }
    }

    /**
     * Etiqueta del turno asignado (ej: "20:15 - 20:30"), para la pantalla de cocina.
     */
    public function getTurnoLabelAttribute()
    {
        $m = $this->microturno;
        return $m ? $m->getFormattedTimeAttribute() : null;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function loyaltyMovements(): HasMany
    {
        return $this->hasMany(UserLoyaltyMovement::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    // Mutators
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }
        });
    }

    // Accessors
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format((float) $this->subtotal, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format((float) $this->total_amount, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'Pendiente',
            self::STATUS_CONFIRMED  => 'Confirmado',
            self::STATUS_PREPARING  => 'En Preparación',
            self::STATUS_READY      => 'Listo',
            self::STATUS_ON_THE_WAY => 'En camino',
            self::STATUS_DELIVERED  => 'Entregado',
            default => 'Desconocido'
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'Pendiente',
            'paid' => 'Pagado',
            'failed' => 'Fallido',
            'refunded' => 'Reembolsado',
            default => 'Desconocido'
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'EFECTIVO',
            'card' => 'TARJETA',
            'transfer' => 'TRANSFERENCIA',
            default => 'EFECTIVO'
        };
    }

    /**
     * Registrar un cambio en el log de auditoría
     */
    public function logChange(string $field, $oldValue, $newValue, string $modifiedBy = 'admin'): void
    {
        $changeLog = $this->change_log ?? [];
        
        $changeLog[] = [
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'modified_by' => $modifiedBy,
            'modified_at' => now()->toISOString(),
        ];
        
        $this->update([
            'change_log' => $changeLog,
            'last_modified_at' => now(),
            'last_modified_by' => $modifiedBy,
        ]);
        
        \Log::info('Order change logged', [
            'order_id' => $this->id,
            'order_number' => $this->order_number,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'modified_by' => $modifiedBy,
        ]);
    }

    /**
     * Obtener el historial de cambios formateado
     */
    public function getChangeHistory(): array
    {
        return $this->change_log ?? [];
    }
}