<?php

namespace Database\Seeders;

use App\Models\WeeklyTurnoConfig;
use Illuminate\Database\Seeder;

class WeeklyTurnoConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WeeklyTurnoConfig::createDefaultConfigs();
    }
}
