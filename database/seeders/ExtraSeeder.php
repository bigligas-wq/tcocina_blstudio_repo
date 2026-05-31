<?php

namespace Database\Seeders;

use App\Models\Extra;
use Illuminate\Database\Seeder;

class ExtraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $extras = [
            ['name' => 'Bacon + Carne + Cheddar', 'price' => 2500.0],
            ['name' => 'Provoleta', 'price' => 1000.0],
            ['name' => 'Queso Azul', 'price' => 1000.0],
            ['name' => 'Champigñón', 'price' => 1000.0],
            ['name' => 'Huevo a la plancha', 'price' => 1000.0],
            ['name' => 'Doble Provolone', 'price' => 1000.0],
            ['name' => 'Porción aros de cebolla + dip', 'price' => 8500.0],
            ['name' => 'Porción papas extra + dip', 'price' => 5500.0],
        ];

        foreach ($extras as $index => $extra) {
            Extra::create([
                'name' => $extra['name'],
                'price' => $extra['price'],
                'is_available' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
