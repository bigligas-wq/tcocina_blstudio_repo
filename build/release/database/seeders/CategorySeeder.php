<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hamburguesas',
                'slug' => 'hamburguesas',
                'description' => 'Deliciosas hamburguesas artesanales con ingredientes frescos',
                'image' => 'hamburguesas.jpg',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Acompañamientos',
                'slug' => 'acompanamientos',
                'description' => 'Papas fritas, aros de cebolla y más acompañamientos',
                'image' => 'acompanamientos.jpg',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Bebidas',
                'slug' => 'bebidas',
                'description' => 'Refrescos, jugos naturales y bebidas heladas',
                'image' => 'bebidas.jpg',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Combos',
                'slug' => 'combos',
                'description' => 'Combos especiales que incluyen hamburguesa, acompañamiento y bebida',
                'image' => 'combos.jpg',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Postres',
                'slug' => 'postres',
                'description' => 'Deliciosos postres para endulzar tu día',
                'image' => 'postres.jpg',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
