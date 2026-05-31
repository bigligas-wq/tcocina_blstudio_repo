<?php

namespace App\Console\Commands;

use App\Models\Extra;
use App\Models\ProductConfiguration;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use App\Models\Sauce;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateProductConfigurations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:product-configurations {--force : Force migration even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from ProductVariant, ProductOption, Sauce, and Extra tables to ProductConfiguration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando migración de configuraciones de productos...');

        // Verificar si ya hay datos
        if (ProductConfiguration::count() > 0 && !$this->option('force')) {
            $this->error('❌ La tabla product_configurations ya tiene datos. Usa --force para sobrescribir.');
            return 1;
        }

        if ($this->option('force')) {
            $this->warn('⚠️  Eliminando datos existentes...');
            ProductConfiguration::truncate();
        }

        $configurations = collect();

        // Migrar ProductVariant
        $this->info('📦 Migrando ProductVariant...');
        $productVariants = ProductVariant::select('name', 'value', 'price_modifier', 'is_available', 'sort_order')
            ->distinct()
            ->get();

        foreach ($productVariants as $variant) {
            $configurations->push([
                'name' => $variant->name,
                'value' => $variant->value,
                'price_modifier' => $variant->price_modifier,
                'is_available' => $variant->is_available,
                'sort_order' => $variant->sort_order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->info("✅ Migrados {$productVariants->count()} ProductVariant");

        // Migrar ProductOption
        $this->info('⚙️ Migrando ProductOption...');
        $productOptions = ProductOption::select('name', 'value', 'price_modifier', 'is_available', 'sort_order')
            ->distinct()
            ->get();

        foreach ($productOptions as $option) {
            $configurations->push([
                'name' => $option->name,
                'value' => $option->value,
                'price_modifier' => $option->price_modifier,
                'is_available' => $option->is_available,
                'sort_order' => $option->sort_order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->info("✅ Migrados {$productOptions->count()} ProductOption");

        // Migrar Sauce (con type como parte del name)
        $this->info('🧂 Migrando Sauce...');
        $sauces = Sauce::select('name', 'type', 'is_available', 'sort_order')->get();

        foreach ($sauces as $sauce) {
            $configurations->push([
                'name' => ucfirst($sauce->type),  // "sauce" -> "Sauce", "dip" -> "Dip"
                'value' => $sauce->name,
                'price_modifier' => 0,  // Las salsas no modifican precio
                'is_available' => $sauce->is_available,
                'sort_order' => $sauce->sort_order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->info("✅ Migrados {$sauces->count()} Sauce");

        // Migrar Extra
        $this->info('➕ Migrando Extra...');
        $extras = Extra::select('name', 'price', 'is_available', 'sort_order')->get();

        foreach ($extras as $extra) {
            $configurations->push([
                'name' => 'Extras',
                'value' => $extra->name,
                'price_modifier' => $extra->price,
                'is_available' => $extra->is_available,
                'sort_order' => $extra->sort_order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->info("✅ Migrados {$extras->count()} Extra");

        // Eliminar duplicados y guardar
        $this->info('🔄 Eliminando duplicados...');
        $uniqueConfigurations = $configurations->unique(function ($item) {
            return $item['name'] . '|' . $item['value'];
        });

        $this->info("💾 Guardando {$uniqueConfigurations->count()} configuraciones únicas...");

        // Insertar en lotes para mejor rendimiento
        $uniqueConfigurations->chunk(100)->each(function ($chunk) {
            ProductConfiguration::insert($chunk->toArray());
        });

        $this->info('🎉 ¡Migración completada exitosamente!');
        $this->table(['Tipo', 'Cantidad'], [
            ['Medallones', ProductConfiguration::where('name', 'Medallones')->count()],
            ['Tipo de Medallón', ProductConfiguration::where('name', 'Tipo de Medallón')->count()],
            ['Aderezos', ProductConfiguration::where('name', 'Aderezos')->count()],
            ['Dip', ProductConfiguration::where('name', 'Dip')->count()],
            ['Dips', ProductConfiguration::where('name', 'Dips')->count()],
            ['Extras', ProductConfiguration::where('name', 'Extras')->count()],
            ['Total', ProductConfiguration::count()],
        ]);

        return 0;
    }
}
