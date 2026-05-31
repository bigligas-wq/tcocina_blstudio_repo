<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoConfig extends Model
{
    protected $fillable = [
        'hora_inicio',
        'hora_fin',
        'duracion_microturno_minutos',
        'max_pedidos_por_microturno',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hora_inicio' => 'datetime:H:i:s',
            'hora_fin' => 'datetime:H:i:s',
            'is_active' => 'boolean',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Métodos estáticos para obtener configuración actual
    public static function getCurrentConfig()
    {
        return self::active()->first() ?? self::createDefault();
    }

    public static function createDefault()
    {
        return self::create([
            'hora_inicio' => '19:30:00',
            'hora_fin' => '22:30:00',
            'duracion_microturno_minutos' => 18,
            'max_pedidos_por_microturno' => 6,
            'is_active' => true,
        ]);
    }

    // Calcular microturnos para una fecha específica
    public function generateMicroturnosForDate($fecha)
    {
        $microturnos = [];
        $horaInicio = \Carbon\Carbon::createFromTimeString($this->hora_inicio);
        $horaFin = \Carbon\Carbon::createFromTimeString($this->hora_fin);

        $currentTime = $horaInicio->copy();
        $sortOrder = 1;

        while ($currentTime->lt($horaFin)) {
            $horaInicioMicroturno = $currentTime->copy();
            $horaFinMicroturno = $currentTime->copy()->addMinutes($this->duracion_microturno_minutos);

            // Si el microturno se extiende más allá del horario de fin, ajustar
            if ($horaFinMicroturno->gt($horaFin)) {
                $horaFinMicroturno = $horaFin->copy();
            }

            $microturnos[] = [
                'fecha' => $fecha,
                'hora_inicio' => $horaInicioMicroturno->format('H:i:s'),
                'hora_fin' => $horaFinMicroturno->format('H:i:s'),
                'capacidad_maxima' => $this->max_pedidos_por_microturno,
                'pedidos_actuales' => 0,
                'is_disponible' => true,
                'sort_order' => $sortOrder,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $currentTime->addMinutes($this->duracion_microturno_minutos);
            $sortOrder++;
        }

        return $microturnos;
    }
}
