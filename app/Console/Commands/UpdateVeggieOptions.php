<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;

class UpdateVeggieOptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-veggie-options';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar las opciones veggie en productos existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Actualizando opciones veggie en productos...');

        try {
            $hamburgerProducts = Product::where('category_id', 1)->get();
            $this->info("Encontrados {$hamburgerProducts->count()} productos de hamburguesas");

            foreach ($hamburgerProducts as $product) {
                // Eliminar opciones veggie existentes
                ProductVariant::where('product_id', $product->id)
                    ->where('name', 'Tipo de Medallón')
                    ->where('value', 'like', '%Veggie%')
                    ->delete();

                // Calcular modificador de precio para veggie
                $veggieTargetPrice = 15500;
                $veggieModifier = (float) $veggieTargetPrice - (float) $product->base_price;

                // Crear nuevas opciones veggie
                $veggieOptions = [
                    [
                        'product_id' => $product->id,
                        'name' => 'Tipo de Medallón',
                        'value' => 'Veggie Tomate Seco Aduki (Rúcula, Albahaca y Oliva)',
                        'price_modifier' => $veggieModifier,
                        'is_available' => true,
                        'sort_order' => 2,
                    ],
                    [
                        'product_id' => $product->id,
                        'name' => 'Tipo de Medallón',
                        'value' => 'Veggie Zanahoria Romero (Arvejas, Yamaní y Chía)',
                        'price_modifier' => $veggieModifier,
                        'is_available' => true,
                        'sort_order' => 3,
                    ]
                ];

                foreach ($veggieOptions as $option) {
                    ProductVariant::create($option);
                }

                $this->info("Actualizadas opciones veggie para {$product->name}");
            }

            $this->info('Actualización completada exitosamente');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error durante la actualización: ' . $e->getMessage());
            return 1;
        }
    }
}
