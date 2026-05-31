<?php

namespace Database\Seeders;

use App\Models\TurnoConfig;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TurnoConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear configuración inicial de turnos
        TurnoConfig::create([
            'hora_inicio' => '19:30:00',
            'hora_fin' => '22:30:00',
            'duracion_microturno_minutos' => 18,
            'max_pedidos_por_microturno' => 6,
            'is_active' => true,
        ]);
    }
}
