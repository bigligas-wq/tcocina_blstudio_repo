<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tablas relacionadas manteniendo integridad referencial según driver
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        DB::table('product_options')->delete();
        DB::table('product_variants')->delete();
        DB::table('products')->delete();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        // Asegurar categoría "Hamburguesas"
        $hamburguesasCategory = Category::firstOrCreate(
            ['slug' => 'hamburguesas'],
            [
                'name' => 'Hamburguesas',
                'description' => 'Deliciosas hamburguesas artesanales con ingredientes frescos',
                'image' => 'hamburguesas.jpg',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $products = [
            [
                'category_id' => $hamburguesasCategory?->id,
                'name' => 'Rayito',
                'slug' => 'rayito',
                'description' => 'Doble carne smasheada (120 grs c/u) con triple cheddar + doble muzarella + champigñones salteados y mayonesa ahumada.',
                'image' => 'rayito.png',
                'base_price' => 15500,
                'is_available' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'allergens' => [],
                'preparation_time' => 15,
            ],
            [
                'category_id' => $hamburguesasCategory?->id,
                'name' => 'Solo Cheese',
                'slug' => 'solo-cheese',
                'description' => 'Doble carne smasheada (120 grs c/u) con triple cheddar y aderezo a elección.',
                'image' => 'solo-cheese.png',
                'base_price' => 15000,
                'is_available' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'allergens' => [],
                'preparation_time' => 15,
            ],
            [
                'category_id' => $hamburguesasCategory?->id,
                'name' => 'La Joya',
                'slug' => 'la-joya',
                'description' => 'Doble carne smasheada (120 grs c/u) con doble cheddar + panceta + provoleta + aros de cebolla y ketchup americano.',
                'image' => 'la-joya.jpg',
                'base_price' => 17000,
                'is_available' => true,
                'is_featured' => true,
                'sort_order' => 3,
                'allergens' => [],
                'preparation_time' => 15,
            ],
            [
                'category_id' => $hamburguesasCategory?->id,
                'name' => 'Cheese Bacon',
                'slug' => 'cheese-bacon',
                'description' => 'Doble carne smasheada (120 grs c/u) con doble cheddar + panceta y aderezo a elección.',
                'image' => 'cheese-bacon.jpg',
                'base_price' => 15500,
                'is_available' => true,
                'is_featured' => true,
                'sort_order' => 4,
                'allergens' => [],
                'preparation_time' => 15,
            ],
            [
                'category_id' => $hamburguesasCategory?->id,
                'name' => '4 Quesos Azul',
                'slug' => '4-quesos-azul',
                'description' => 'Doble carne smasheada (120 grs c/u) con doble cheddar + provoleta + muzzarella + queso azul.',
                'image' => '4-quesos-azul.png',
                'base_price' => 15500,
                'is_available' => true,
                'is_featured' => false,
                'sort_order' => 5,
                'allergens' => [],
                'preparation_time' => 15,
            ],
            [
                'category_id' => $hamburguesasCategory?->id,
                'name' => '4 Quesos Amarilla',
                'slug' => '4-quesos-amarilla',
                'description' => 'Doble carne smasheada (120 grs c/u) con doble cheddar + provoleta + doble provolone + muzzarella.',
                'image' => '4-quesos-amarilla.jpg',
                'base_price' => 15500,
                'is_available' => true,
                'is_featured' => false,
                'sort_order' => 6,
                'allergens' => [],
                'preparation_time' => 15,
            ],
            [
                'category_id' => $hamburguesasCategory?->id,
                'name' => 'Playita',
                'slug' => 'playita',
                'description' => 'Doble carne smasheada (120 grs c/u) con doble provolone + doble jamón cocido natural y aderezo a elección.',
                'image' => 'playita.png',
                'base_price' => 16500,
                'is_available' => true,
                'is_featured' => false,
                'sort_order' => 7,
                'allergens' => [],
                'preparation_time' => 15,
            ],
            [
                'category_id' => $hamburguesasCategory?->id,
                'name' => 'Piruco',
                'slug' => 'piruco',
                'description' => 'Doble carne smasheada (120 grs c/u) con doble provolone + champigñones salteados + muzzarella + queso azul + cebolla caramelizada.',
                'image' => 'piruco.png',
                'base_price' => 15500,
                'is_available' => true,
                'is_featured' => false,
                'sort_order' => 8,
                'allergens' => [],
                'preparation_time' => 15,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Opciones: Extras con costo y Aderezos sin costo para todas las hamburguesas
        $createdProducts = Product::whereIn('slug', array_map(fn($p) => $p['slug'], $products))->get();

        $extras = [
            ['name' => 'Extras', 'value' => 'Bacon + Carne + Cheddar', 'price_modifier' => 2500, 'sort_order' => 1],
            ['name' => 'Extras', 'value' => 'Provoleta', 'price_modifier' => 1000, 'sort_order' => 2],
            ['name' => 'Extras', 'value' => 'Queso Azul', 'price_modifier' => 1000, 'sort_order' => 3],
            ['name' => 'Extras', 'value' => 'Champigñón', 'price_modifier' => 1000, 'sort_order' => 4],
            ['name' => 'Extras', 'value' => 'Huevo a la plancha', 'price_modifier' => 1000, 'sort_order' => 5],
            ['name' => 'Extras', 'value' => 'Doble Provolone', 'price_modifier' => 1000, 'sort_order' => 6],
            ['name' => 'Extras', 'value' => 'Porción aros de cebolla + dip', 'price_modifier' => 8500, 'sort_order' => 7],
            ['name' => 'Extras', 'value' => 'Porción papas extra + dip', 'price_modifier' => 5500, 'sort_order' => 8],
        ];

        $aderezos = [
            ['name' => 'Aderezos', 'value' => 'Cheddar', 'price_modifier' => 0, 'sort_order' => 1],
            ['name' => 'Aderezos', 'value' => 'Ketchup', 'price_modifier' => 0, 'sort_order' => 2],
            ['name' => 'Aderezos', 'value' => 'Barbacoa', 'price_modifier' => 0, 'sort_order' => 3],
            ['name' => 'Aderezos', 'value' => 'Mostaza', 'price_modifier' => 0, 'sort_order' => 4],
            ['name' => 'Aderezos', 'value' => 'Mayonesa', 'price_modifier' => 0, 'sort_order' => 5],
            ['name' => 'Aderezos', 'value' => 'Ketchup americano', 'price_modifier' => 0, 'sort_order' => 6],
            ['name' => 'Aderezos', 'value' => 'Mostaza y miel', 'price_modifier' => 0, 'sort_order' => 7],
            ['name' => 'Aderezos', 'value' => 'Mayonesa ahumada', 'price_modifier' => 0, 'sort_order' => 8],
            ['name' => 'Aderezos', 'value' => 'Mayonesa de ajo', 'price_modifier' => 0, 'sort_order' => 9],
            ['name' => 'Aderezos', 'value' => 'Mayonesa picante', 'price_modifier' => 0, 'sort_order' => 10],
        ];

        foreach ($createdProducts as $productModel) {
            // Variantes de Medallones (Simple, Doble, Triple)
            $variantsMedallones = [
                ['name' => 'Medallones', 'value' => 'Simple', 'price_modifier' => -2000, 'sort_order' => 1],
                ['name' => 'Medallones', 'value' => 'Doble', 'price_modifier' => 0, 'sort_order' => 2],
                ['name' => 'Medallones', 'value' => 'Triple', 'price_modifier' => 2500, 'sort_order' => 3],
            ];
            foreach ($variantsMedallones as $v) {
                ProductVariant::create([
                    'product_id' => $productModel->id,
                    'name' => $v['name'],
                    'value' => $v['value'],
                    'price_modifier' => $v['price_modifier'],
                    'is_available' => true,
                    'sort_order' => $v['sort_order'],
                ]);
            }

            // Variantes de Tipo de Medallón (Carne $0, Veggie ajusta a precio TXT $15.500)
            $veggieTargetPrice = 15500;
            $currentBasePrice = (float) $productModel->base_price;
            $veggieModifier = $veggieTargetPrice - $currentBasePrice;  // puede ser positivo o negativo

            ProductVariant::create([
                'product_id' => $productModel->id,
                'name' => 'Tipo de Medallón',
                'value' => 'Carne',
                'price_modifier' => 0,
                'is_available' => true,
                'sort_order' => 1,
            ]);

            ProductVariant::create([
                'product_id' => $productModel->id,
                'name' => 'Tipo de Medallón',
                'value' => 'Veggie',
                'price_modifier' => $veggieModifier,
                'is_available' => true,
                'sort_order' => 2,
            ]);

            foreach ($extras as $opt) {
                ProductOption::create([
                    'product_id' => $productModel->id,
                    'name' => $opt['name'],
                    'value' => $opt['value'],
                    'price_modifier' => $opt['price_modifier'],
                    'is_available' => true,
                    'sort_order' => $opt['sort_order'],
                ]);
            }

            foreach ($aderezos as $opt) {
                ProductOption::create([
                    'product_id' => $productModel->id,
                    'name' => $opt['name'],
                    'value' => $opt['value'],
                    'price_modifier' => $opt['price_modifier'],
                    'is_available' => true,
                    'sort_order' => $opt['sort_order'],
                ]);
            }
        }
    }
}
