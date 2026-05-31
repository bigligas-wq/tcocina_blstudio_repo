<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyTurnoConfig extends Model
{
    protected $fillable = [
        'day_of_week',
        'hora_inicio',
        'hora_fin',
        'duracion_microturno_minutos',
        'max_pedidos_por_microturno',
        'max_hamburguesas',
        'max_acompañamientos',
        'is_active',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'hora_inicio' => 'datetime:H:i:s',
            'hora_fin' => 'datetime:H:i:s',
            'is_active' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeForDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    // Métodos estáticos
    public static function getConfigForDay($dayOfWeek)
    {
        return self::forDay($dayOfWeek)->active()->enabled()->first();
    }

    public static function getConfigForDate($date)
    {
        $dayOfWeek = strtolower(\Carbon\Carbon::parse($date)->format('l'));
        return self::getConfigForDay($dayOfWeek);
    }

    public static function createDefaultConfigs()
    {
        $defaultConfigs = [
            'monday' => ['hora_inicio' => '19:30:00', 'hora_fin' => '22:30:00', 'duracion_microturno_minutos' => 18, 'max_pedidos_por_microturno' => 6],
            'tuesday' => ['hora_inicio' => '19:30:00', 'hora_fin' => '22:30:00', 'duracion_microturno_minutos' => 18, 'max_pedidos_por_microturno' => 6],
            'wednesday' => ['hora_inicio' => '19:30:00', 'hora_fin' => '22:30:00', 'duracion_microturno_minutos' => 18, 'max_pedidos_por_microturno' => 6],
            'thursday' => ['hora_inicio' => '19:30:00', 'hora_fin' => '22:30:00', 'duracion_microturno_minutos' => 18, 'max_pedidos_por_microturno' => 6],
            'friday' => ['hora_inicio' => '19:30:00', 'hora_fin' => '22:30:00', 'duracion_microturno_minutos' => 18, 'max_pedidos_por_microturno' => 6],
            'saturday' => ['hora_inicio' => '19:30:00', 'hora_fin' => '22:30:00', 'duracion_microturno_minutos' => 18, 'max_pedidos_por_microturno' => 6],
            'sunday' => ['hora_inicio' => '19:30:00', 'hora_fin' => '22:30:00', 'duracion_microturno_minutos' => 18, 'max_pedidos_por_microturno' => 6],
        ];

        foreach ($defaultConfigs as $day => $config) {
            self::updateOrCreate(
                ['day_of_week' => $day],
                array_merge($config, [
                    'is_active' => true,
                    'is_enabled' => true,
                ])
            );
        }
    }

    public static function getAllConfigs()
    {
        $configs = self::active()->orderByRaw("
            CASE day_of_week 
                WHEN 'monday' THEN 1
                WHEN 'tuesday' THEN 2
                WHEN 'wednesday' THEN 3
                WHEN 'thursday' THEN 4
                WHEN 'friday' THEN 5
                WHEN 'saturday' THEN 6
                WHEN 'sunday' THEN 7
            END
        ")->get();

        // Si no hay configuraciones, crear las por defecto
        if ($configs->isEmpty()) {
            self::createDefaultConfigs();
            return self::getAllConfigs();
        }

        return $configs;
    }

    /** Para Configuración: devuelve siempre los 7 días (sin filtrar por active) para poder editar is_enabled. */
    public static function getConfigsForSettings()
    {
        $configs = self::orderByRaw("
            CASE day_of_week 
                WHEN 'monday' THEN 1
                WHEN 'tuesday' THEN 2
                WHEN 'wednesday' THEN 3
                WHEN 'thursday' THEN 4
                WHEN 'friday' THEN 5
                WHEN 'saturday' THEN 6
                WHEN 'sunday' THEN 7
            END
        ")->get();

        if ($configs->count() < 7) {
            self::createDefaultConfigs();
            return self::getConfigsForSettings();
        }

        return $configs;
    }

    // Accessors
    public function getDayNameAttribute(): string
    {
        $dayNames = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo',
        ];

        return $dayNames[$this->day_of_week] ?? ucfirst($this->day_of_week);
    }

    public function getFormattedTimeAttribute(): string
    {
        $inicio = \Carbon\Carbon::createFromTimeString($this->hora_inicio);
        $fin = \Carbon\Carbon::createFromTimeString($this->hora_fin);
        return $inicio->format('H:i') . ' - ' . $fin->format('H:i');
    }

    // Método para generar microturnos para una fecha específica
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
