<?php

namespace Database\Seeders;

use App\Models\Sauce;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SauceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Aderezos existentes
        $aderezos = [
            'Cheddar',
            'Ketchup',
            'Barbacoa',
            'Mostaza',
            'Mayonesa',
            'Ketchup americano',
            'Mostaza y miel',
            'Mayonesa ahumada',
            'Mayonesa de ajo',
            'Mayonesa picante',
        ];

        foreach ($aderezos as $index => $aderezo) {
            Sauce::create([
                'name' => $aderezo,
                'type' => 'sauce',
                'is_available' => true,
                'sort_order' => $index + 1,
            ]);
        }

        // Dips iniciales (pueden agregarse más desde la configuración)
        $dips = [
            'Salsa de ajo',
            'Salsa picante',
            'Salsa de queso',
            'Salsa ranch',
        ];

        foreach ($dips as $index => $dip) {
            Sauce::create([
                'name' => $dip,
                'type' => 'dip',
                'is_available' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
