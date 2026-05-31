<?php

namespace App\Console\Commands;

use App\Models\Extra;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Console\Command;

class SyncExtrasWithProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extras:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar extras con productos de hamburguesas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sincronizando extras con productos...');

        try {
            $hamburgerProducts = Product::where('category_id', 1)->get();
            $this->info("Encontrados {$hamburgerProducts->count()} productos de hamburguesas");

            foreach ($hamburgerProducts as $product) {
                // Eliminar opciones de extras existentes
                ProductOption::where('product_id', $product->id)
                    ->where('name', 'Extras')
                    ->delete();

                // Obtener extras actuales
                $extras = Extra::available()->ordered()->get();

                // Crear opciones de extras
                foreach ($extras as $extra) {
                    ProductOption::create([
                        'product_id' => $product->id,
                        'name' => 'Extras',
                        'value' => $extra->name,
                        'price_modifier' => $extra->price,
                        'is_available' => $extra->is_available,
                        'sort_order' => $extra->sort_order,
                    ]);
                }

                $this->info("Sincronizados {$extras->count()} extras con {$product->name}");
            }

            $this->info('Sincronización completada exitosamente');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error durante la sincronización: ' . $e->getMessage());
            return 1;
        }
    }
}
