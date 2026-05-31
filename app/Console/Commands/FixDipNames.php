<?php

namespace App\Console\Commands;

use App\Models\ProductConfiguration;
use Illuminate\Console\Command;

class FixDipNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:dip-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Dip names: Delete Dips (plural), create Dip (singular), and update Dip Extra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Corrigiendo nombres de Dip...');

        // 1. Get existing Dips (plural) values before deleting
        $existingDips = ProductConfiguration::where('name', 'Dips')->get();

        if ($existingDips->isEmpty()) {
            $this->error('❌ No se encontraron registros con name "Dips".');
            return 1;
        }

        $this->info("✅ Encontrados {$existingDips->count()} registros con name 'Dips'");

        // 2. Delete all Dips (plural) records
        $deleted = ProductConfiguration::where('name', 'Dips')->delete();
        $this->info("🗑️  Eliminados {$deleted} registros con name 'Dips'");

        // 3. Create Dip (singular) records with the same values
        $maxSortOrder = ProductConfiguration::where('name', 'Dip')->max('sort_order') ?? 0;
        $created = 0;

        foreach ($existingDips as $index => $dip) {
            // Check if this Dip (singular) already exists
            $exists = ProductConfiguration::where('name', 'Dip')
                ->where('value', $dip->value)
                ->exists();

            if (!$exists) {
                ProductConfiguration::create([
                    'name' => 'Dip',
                    'value' => $dip->value,
                    'price_modifier' => $dip->price_modifier,
                    'is_available' => $dip->is_available,
                    'sort_order' => $maxSortOrder + $index + 1,
                ]);
                $created++;
                $this->line("  ✅ Creado Dip: {$dip->value}");
            } else {
                $this->line("  ⚠️  Ya existe Dip: {$dip->value}");
            }
        }

        $this->info("✅ Creados {$created} registros con name 'Dip'");

        // 4. Delete existing Dip Extra and recreate with Dip values
        $dipExtraDeleted = ProductConfiguration::where('name', 'Dip Extra')->delete();
        $this->info("🗑️  Eliminados {$dipExtraDeleted} registros con name 'Dip Extra'");

        // Get Dip (singular) values for Dip Extra
        $dipSingular = ProductConfiguration::where('name', 'Dip')
            ->where('is_available', true)
            ->get();

        $maxDipExtraSortOrder = 0;
        $dipExtraCreated = 0;

        foreach ($dipSingular as $index => $dip) {
            ProductConfiguration::create([
                'name' => 'Dip Extra',
                'value' => $dip->value,
                'price_modifier' => 1000,  // Fixed price for Dip Extra
                'is_available' => true,
                'sort_order' => $maxDipExtraSortOrder + $index + 1,
            ]);
            $dipExtraCreated++;
            $this->line("  ✅ Creado Dip Extra: {$dip->value} (+\$1000)");
        }

        $this->info("✅ Creados {$dipExtraCreated} registros con name 'Dip Extra'");

        // Show summary
        $this->info('🎉 ¡Corrección completada!');
        $this->table(
            ['Tipo', 'Cantidad'],
            [
                ['Dip (singular)', ProductConfiguration::where('name', 'Dip')->count()],
                ['Dip Extra', ProductConfiguration::where('name', 'Dip Extra')->count()],
            ]
        );

        return 0;
    }
}
