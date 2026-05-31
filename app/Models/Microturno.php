<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Microturno extends Model
{
    protected $fillable = [
        'fecha',
        'hora_inicio',
        'hora_fin',
        'capacidad_maxima',
        'pedidos_actuales',
        'is_disponible',
        'sort_order',
    ];

    protected $appends = [
        'formatted_time',
        'capacidad_restante',
        'pedidos_activos'
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'hora_inicio' => 'datetime:H:i:s',
            'hora_fin' => 'datetime:H:i:s',
            'is_disponible' => 'boolean',
        ];
    }

    // Scopes
    public function scopeDisponibles($query)
    {
        return $query->where('is_disponible', true);
    }

    public function scopeParaFecha($query, $fecha)
    {
        return $query->where('fecha', $fecha);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeConCapacidad($query)
    {
        $activeStatuses = implode('","', Order::ACTIVE_STATUSES);
        return $query->whereRaw('(SELECT COUNT(*) FROM orders WHERE orders.microturno_id = microturnos.id AND orders.status IN ("' . $activeStatuses . '")) < capacidad_maxima');
    }

    // Relaciones
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Métodos
    public function tieneCapacidad(): bool
    {
        $pedidosActivos = $this->orders()->whereIn('status', Order::ACTIVE_STATUSES)->count();
        return $pedidosActivos < $this->capacidad_maxima;
    }

    public function estaDisponible(): bool
    {
        return $this->tieneCapacidad();
    }

    public function incrementarPedidos(): void
    {
        $this->increment('pedidos_actuales');
        $this->refresh();

        // Si se llenó la capacidad, marcar como no disponible
        if (!$this->tieneCapacidad()) {
            $this->update(['is_disponible' => false]);
        }
    }

    public function decrementarPedidos(): void
    {
        $this->decrement('pedidos_actuales');
        $this->refresh();

        // Si ahora tiene capacidad, marcar como disponible
        if ($this->tieneCapacidad()) {
            $this->update(['is_disponible' => true]);
        }
    }

    public function getFormattedTimeAttribute(): string
    {
        $inicio = \Carbon\Carbon::createFromTimeString($this->hora_inicio);
        $fin = \Carbon\Carbon::createFromTimeString($this->hora_fin);

        return $inicio->format('H:i') . ' - ' . $fin->format('H:i');
    }

    public function getCapacidadRestanteAttribute(): int
    {
        $pedidosActivos = $this->orders()->whereIn('status', Order::ACTIVE_STATUSES)->count();
        return max(0, $this->capacidad_maxima - $pedidosActivos);
    }

    public function getPedidosActivosAttribute(): int
    {
        return $this->orders()->whereIn('status', Order::ACTIVE_STATUSES)->count();
    }

    // Mantener este método por compatibilidad
    public function getPedidosConfirmadosAttribute(): int
    {
        return $this->pedidos_activos;
    }

    // Método estático para obtener microturnos disponibles para una fecha
    public static function getDisponiblesParaFecha($fecha)
    {
        return self::paraFecha($fecha)
            ->disponibles()
            ->conCapacidad()
            ->ordenados()
            ->get();
    }

    // Método estático para crear microturnos para una fecha
    public static function crearParaFecha($fecha)
    {
        $dayOfWeek = strtolower(\Carbon\Carbon::parse($fecha)->format('l'));
        $config = \App\Models\WeeklyTurnoConfig::getConfigForDay($dayOfWeek);

        // Si no hay configuración para este día, no crear microturnos
        if (!$config || !$config->is_enabled) {
            return collect();
        }

        $microturnosData = $config->generateMicroturnosForDate($fecha);

        // Eliminar microturnos existentes para esta fecha
        self::paraFecha($fecha)->delete();

        // Crear los microturnos con la configuración semanal actual
        self::insert($microturnosData);

        return self::paraFecha($fecha)->get();
    }
}
