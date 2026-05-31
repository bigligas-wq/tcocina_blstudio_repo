<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\Sauce;
use Illuminate\Console\Command;

class SyncSaucesWithProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sauces:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar aderezos y dips con las opciones de productos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronización de aderezos y dips...');

        try {
            // Obtener todos los productos de hamburguesas y acompañamientos
            $hamburgerProducts = Product::where('category_id', 1)->get();
            $accompanimentProducts = Product::where('category_id', 2)->get();
            $allProducts = $hamburgerProducts->merge($accompanimentProducts);
            $this->info("Encontrados {$hamburgerProducts->count()} productos de hamburguesas y {$accompanimentProducts->count()} productos de acompañamientos");

            $totalOptions = 0;

            foreach ($allProducts as $product) {
                $this->line("Procesando: {$product->name}");

                // Eliminar opciones de aderezos y dips existentes
                $deletedCount = ProductOption::where('product_id', $product->id)
                    ->whereIn('name', ['Aderezos', 'Dips'])
                    ->delete();

                if ($deletedCount > 0) {
                    $this->line("  - Eliminadas {$deletedCount} opciones existentes");
                }

                // Obtener aderezos y dips actuales
                $sauces = Sauce::available()->sauces()->ordered()->get();
                $dips = Sauce::available()->dips()->ordered()->get();

                // Crear opciones de aderezos
                foreach ($sauces as $sauce) {
                    ProductOption::create([
                        'product_id' => $product->id,
                        'name' => 'Aderezos',
                        'value' => $sauce->name,
                        'price_modifier' => 0,
                        'is_available' => true,
                        'sort_order' => $sauce->sort_order,
                    ]);
                    $totalOptions++;
                }

                // Crear opciones de dips
                foreach ($dips as $dip) {
                    ProductOption::create([
                        'product_id' => $product->id,
                        'name' => 'Dips',
                        'value' => $dip->name,
                        'price_modifier' => 0,
                        'is_available' => true,
                        'sort_order' => $dip->sort_order,
                    ]);
                    $totalOptions++;
                }

                $this->line('  - Agregadas ' . ($sauces->count() + $dips->count()) . ' opciones');
            }

            $this->info('✅ Sincronización completada exitosamente!');
            $this->info("Total de opciones creadas: {$totalOptions}");
        } catch (\Exception $e) {
            $this->error('❌ Error durante la sincronización: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
