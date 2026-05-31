<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MigrateImagesToPublicSeeder extends Seeder
{
    /**
     * Migrar imágenes de storage a public/images
     */
    public function run(): void
    {
        $this->command->info('🔄 Iniciando migración de imágenes de storage a public...');

        // Crear directorio public/images/products si no existe
        $publicProductsDir = public_path('images/products');
        if (!File::exists($publicProductsDir)) {
            File::makeDirectory($publicProductsDir, 0755, true);
            $this->command->info("✅ Creado directorio: {$publicProductsDir}");
        }

        // Crear directorio public/images si no existe (para logo)
        $publicImagesDir = public_path('images');
        if (!File::exists($publicImagesDir)) {
            File::makeDirectory($publicImagesDir, 0755, true);
            $this->command->info("✅ Creado directorio: {$publicImagesDir}");
        }

        // Migrar logo
        $logoSource = storage_path('app/public/log.png');
        $logoTarget = public_path('images/log.png');
        
        if (File::exists($logoSource)) {
            File::copy($logoSource, $logoTarget);
            $this->command->info('✅ Logo migrado: log.png');
        }

        // Obtener productos con imágenes
        $products = Product::whereNotNull('image')->get();
        $migrated = 0;
        $updated = 0;
        $errors = 0;

        foreach ($products as $product) {
            $currentImage = $product->image;

            // Si ya está en el formato correcto, saltar
            if (str_starts_with($currentImage, 'products/')) {
                $this->command->line("⏭️  Saltando {$product->name}: ya en formato correcto ({$currentImage})");
                continue;
            }

            // Determinar ruta de origen
            $sourceFile = null;
            if (str_starts_with($currentImage, 'products/')) {
                // Ya está en storage/app/public/products/
                $sourceFile = storage_path("app/public/{$currentImage}");
            } else {
                // Buscar en storage/app/public/products/
                $sourceFile = storage_path("app/public/products/{$currentImage}");
            }

            if (!File::exists($sourceFile)) {
                $this->command->error("❌ Imagen no encontrada: {$sourceFile}");
                $errors++;
                continue;
            }

            try {
                // Copiar a public/images/products/
                $targetFile = public_path("images/products/{$currentImage}");
                File::copy($sourceFile, $targetFile);

                // Actualizar ruta en la base de datos
                $product->update(['image' => "products/{$currentImage}"]);

                $this->command->info("✅ Migrado: {$product->name} -> products/{$currentImage}");
                $migrated++;
                $updated++;

            } catch (\Exception $e) {
                $this->command->error("❌ Error migrando {$product->name}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->command->info("\n📊 Resumen de migración:");
        $this->command->info("✅ Imágenes migradas: {$migrated}");
        $this->command->info("📝 Registros actualizados: {$updated}");
        $this->command->info("❌ Errores: {$errors}");

        if ($migrated > 0) {
            $this->command->info("\n🎉 Migración completada!");
            $this->command->info("📁 Las imágenes ahora están en: public/images/products/");
            $this->command->info("🌐 URLs: /images/products/nombre-imagen.jpg");
        }
    }
}
