<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LabOrder extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'estado',
        'total_usd',
        'comprobante_path',
        'whatsapp_enviado_at',
        'confirmado_at',
        'activado_at',
    ];

    protected $casts = [
        'total_usd'           => 'decimal:2',
        'whatsapp_enviado_at' => 'datetime',
        'confirmado_at'       => 'datetime',
        'activado_at'         => 'datetime',
    ];

    public const ESTADOS = [
        'pendiente_pago'  => 'Pendiente de pago',
        'confirmado'      => 'Confirmado',
        'en_proceso'      => 'En proceso',
        'activo_parcial'  => 'Parcialmente activo',
        'activo'          => 'Activo',
        'cancelado'       => 'Cancelado',
    ];

    protected static function booted(): void
    {
        static::creating(function (LabOrder $order) {
            if (!$order->order_number) {
                $order->order_number = 'LAB-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabOrderItem::class);
    }

    public function getComprobanteUrlAttribute(): ?string
    {
        return $this->comprobante_path ? asset('storage/' . $this->comprobante_path) : null;
    }

    public function recomputeEstado(): void
    {
        $items = $this->items()->get();
        if ($items->isEmpty()) {
            return;
        }

        $allActivos = $items->every(fn ($i) => $i->estado === 'activo');
        $someActivos = $items->contains(fn ($i) => $i->estado === 'activo');

        if ($allActivos) {
            $this->estado = 'activo';
            if (!$this->activado_at) {
                $this->activado_at = now();
            }
        } elseif ($someActivos) {
            $this->estado = 'activo_parcial';
        }

        $this->save();
    }
}
