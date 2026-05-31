<?php

namespace App\Console\Commands;

use App\Models\ProductConfiguration;
use Illuminate\Console\Command;

class CreateDipExtraOptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:dip-extra-options';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Dip Extra options with price modifier of 1000';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Creando opciones de Dip Extra...');

        // Get existing Dip values
        $existingDips = ProductConfiguration::where('name', 'Dips')
            ->where('is_available', true)
            ->get();

        if ($existingDips->isEmpty()) {
            $this->error('❌ No se encontraron opciones de Dip existentes.');
            return 1;
        }

        $this->info("✅ Encontradas {$existingDips->count()} opciones de Dip existentes");

        // Get the highest sort_order for Dip Extra
        $maxSortOrder = ProductConfiguration::where('name', 'Dip Extra')->max('sort_order') ?? 0;

        $created = 0;
        foreach ($existingDips as $index => $dip) {
            // Check if this Dip Extra already exists
            $exists = ProductConfiguration::where('name', 'Dip Extra')
                ->where('value', $dip->value)
                ->exists();

            if (!$exists) {
                ProductConfiguration::create([
                    'name' => 'Dip Extra',
                    'value' => $dip->value,
                    'price_modifier' => 1000,
                    'is_available' => true,
                    'sort_order' => $maxSortOrder + $index + 1,
                ]);
                $created++;
                $this->line("  ✅ Creado: {$dip->value}");
            } else {
                $this->line("  ⚠️  Ya existe: {$dip->value}");
            }
        }

        $this->info("🎉 ¡Completado! Se crearon {$created} nuevas opciones de Dip Extra");

        // Show summary
        $totalDipExtra = ProductConfiguration::where('name', 'Dip Extra')->count();
        $this->table(
            ['Tipo', 'Cantidad'],
            [
                ['Dip Extra', $totalDipExtra],
            ]
        );

        return 0;
    }
}
