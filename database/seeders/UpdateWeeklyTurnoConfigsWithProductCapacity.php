<?php

namespace Database\Seeders;

use App\Models\WeeklyTurnoConfig;
use Illuminate\Database\Seeder;

class UpdateWeeklyTurnoConfigsWithProductCapacity extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Actualizar todas las configuraciones existentes con valores por defecto
        WeeklyTurnoConfig::whereNull('max_hamburguesas')
            ->orWhereNull('max_acompañamientos')
            ->update([
                'max_hamburguesas' => 6,
                'max_acompañamientos' => 6
            ]);
    }
}
