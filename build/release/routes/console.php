<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:import', function () {
    $this->info('Importando productos desde carpeta productos/...');

    $basePath = base_path('productos');
    $dataFile = $basePath . DIRECTORY_SEPARATOR . 'Menu Completo txt.txt';
    $publicImagesPath = public_path('images' . DIRECTORY_SEPARATOR . 'products');

    if (!file_exists($dataFile)) {
        $this->error('No se encontró el archivo de datos: ' . $dataFile);
        return 1;
    }

    if (!is_dir($publicImagesPath)) {
        @mkdir($publicImagesPath, 0775, true);
    }

    $text = file_get_contents($dataFile);
    $lines = preg_split('/\r?\n/', $text);

    $section = null;
    $products = [];
    $current = null;
    $veggieOptions = [];
    $extras = [];
    $aderezos = [];

    foreach ($lines as $raw) {
        $line = trim($raw);
        if ($line === '')
            continue;

        if (str_starts_with($line, '---')) {
            if (str_contains($line, 'LANDING'))
                $section = 'landing';
            elseif (str_contains($line, 'Veggie'))
                $section = 'veggie';
            elseif (str_contains($line, 'EXTRAS'))
                $section = 'extras';
            elseif (str_contains($line, 'ADEREZOS'))
                $section = 'aderezos';
            else
                $section = null;
            continue;
        }

        if ($section === 'landing') {
            if (str_starts_with($line, 'Precio:')) {
                if ($current) {
                    $price = (float) str_replace(['Precio:', '$', '.', ' '], ['', '', '', ''], $line);
                    $current['price'] = $price;
                    $products[] = $current;
                    $current = null;
                }
            } else {
                if ($current === null) {
                    $current = ['name' => $line, 'description' => null, 'price' => null];
                } else {
                    $current['description'] = $line;
                }
            }
        } elseif ($section === 'veggie') {
            if (str_starts_with($line, '- ')) {
                $veggieOptions[] = substr($line, 2);
            }
        } elseif ($section === 'extras') {
            if (str_contains($line, ':')) {
                [$n, $p] = array_map('trim', explode(':', $line, 2));
                // Omitir este extra específico (se maneja con Tamaño Triple)
                if (strcasecmp($n, 'Bacon + Carne + Cheddar') === 0) {
                    continue;
                }
                $price = (float) str_replace(['$', '.', ' '], ['', '', ''], $p);
                $extras[] = ['name' => $n, 'price' => $price];
            }
        } elseif ($section === 'aderezos') {
            // separar por comas
            $parts = array_filter(array_map('trim', explode(',', str_replace(['- '], [''], $line))));
            foreach ($parts as $p) {
                if ($p !== '')
                    $aderezos[] = $p;
            }
        }
    }

    // No crear producto Veggie; se usará como variante "Medallón" en hamburguesas

    // Normalizar aderezos únicos
    $aderezos = array_values(array_unique($aderezos));

    DB::beginTransaction();
    try {
        // Limpiar variantes y opciones primero por si no hay FK con cascade
        DB::table('product_options')->delete();
        DB::table('product_variants')->delete();
        DB::table('products')->delete();

        $sort = 0;
        foreach ($products as $p) {
            $name = $p['name'];
            $slug = Str::slug($name);
            $desc = $p['description'] ?? '';
            $price = $p['price'] ?? 0;

            // Buscar imagen por coincidencia de nombre
            $imageFile = null;
            $dirFiles = @scandir($basePath) ?: [];
            foreach ($dirFiles as $f) {
                if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'])) {
                    $fn = strtolower(pathinfo($f, PATHINFO_FILENAME));
                    if (str_contains($fn, strtolower(str_replace('-', ' ', $slug))) || str_contains($fn, strtolower(str_replace('-', '', $slug)))) {
                        $imageFile = $f;
                        break;
                    }
                }
            }

            $savedImage = null;
            if ($imageFile) {
                $ext = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));
                $target = $slug . '.' . $ext;
                @copy($basePath . DIRECTORY_SEPARATOR . $imageFile, $publicImagesPath . DIRECTORY_SEPARATOR . $target);
                $savedImage = $target;
            }

            // Insertar producto (usando columnas reales: name, slug, description, price, image, category)
            $productId = DB::table('products')->insertGetId([
                'name' => $name,
                'description' => $desc,
                'price' => $price,
                'image' => $savedImage,
                'category' => 'Hamburguesas',
                'is_available' => 1,
                'sort_order' => $sort++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Variantes comunes: Tamaño (sin costo adicional)
            $sizeValues = ['Simple' => -2000, 'Doble' => 0, 'Triple' => 2500];
            $idx = 0;
            foreach ($sizeValues as $size => $modifier) {
                DB::table('product_variants')->insert([
                    'product_id' => $productId,
                    'name' => 'Tamaño',
                    'value' => $size,
                    'price_modifier' => $modifier,
                    'is_available' => 1,
                    'sort_order' => $idx++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Variante: Medallón (Carne por defecto + opciones veggie)
            $pattyValues = array_merge(['Carne'], $veggieOptions);
            foreach ($pattyValues as $idx => $patty) {
                DB::table('product_variants')->insert([
                    'product_id' => $productId,
                    'name' => 'Medallón',
                    'value' => $patty,
                    'price_modifier' => 0,
                    'is_available' => 1,
                    'sort_order' => $idx,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Opciones comunes: Aderezos (sin costo)
            foreach ($aderezos as $idx => $opt) {
                DB::table('product_options')->insert([
                    'product_id' => $productId,
                    'name' => 'Aderezos',
                    'value' => $opt,
                    'price_modifier' => 0,
                    'is_available' => 1,
                    'sort_order' => $idx,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Opciones comunes: Extras (con costo)
            foreach ($extras as $idx => $ex) {
                DB::table('product_options')->insert([
                    'product_id' => $productId,
                    'name' => 'Extras',
                    'value' => $ex['name'],
                    'price_modifier' => $ex['price'],
                    'is_available' => 1,
                    'sort_order' => $idx,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Ya no se crean opciones por veggie aquí; está incluido en la variante Medallón
        }

        DB::commit();
        $this->info('Importación completada. Productos creados: ' . count($products));
        return 0;
    } catch (\Throwable $e) {
        DB::rollBack();
        $this->error('Error importando productos: ' . $e->getMessage());
        return 1;
    }
})->purpose('Importar productos desde /productos con variantes y opciones');

Artisan::command('products:delete {name}', function (string $name) {
    $this->info('Eliminando producto: ' . $name);

    DB::beginTransaction();
    try {
        $ids = DB::table('products')->where('name', $name)->pluck('id');
        if ($ids->isEmpty()) {
            $this->warn('No se encontró el producto con ese nombre.');
            DB::rollBack();
            return 0;
        }

        DB::table('product_options')->whereIn('product_id', $ids)->delete();
        DB::table('product_variants')->whereIn('product_id', $ids)->delete();
        DB::table('products')->whereIn('id', $ids)->delete();

        DB::commit();
        $this->info('Producto eliminado correctamente.');
        return 0;
    } catch (\Throwable $e) {
        DB::rollBack();
        $this->error('Error eliminando producto: ' . $e->getMessage());
        return 1;
    }
})->purpose('Eliminar un producto por nombre, incluyendo variantes y opciones');

Artisan::command('products:update-one {name}', function (string $name) {
    $this->info('Actualizando producto: ' . $name);

    $basePath = base_path('productos');
    $dataFile = $basePath . DIRECTORY_SEPARATOR . 'Menu Completo txt.txt';
    $publicImagesPath = public_path('images' . DIRECTORY_SEPARATOR . 'products');

    if (!file_exists($dataFile)) {
        $this->error('No se encontró el archivo de datos: ' . $dataFile);
        return 1;
    }

    $text = file_get_contents($dataFile);
    $lines = preg_split('/\r?\n/', $text);

    $section = null;
    $current = null;
    $foundData = null;

    foreach ($lines as $raw) {
        $line = trim($raw);
        if ($line === '')
            continue;

        if (str_starts_with($line, '---')) {
            if (str_contains($line, 'LANDING'))
                $section = 'landing';
            else
                $section = null;
            continue;
        }

        if ($section === 'landing') {
            if ($current === null) {
                // posible nombre
                $current = ['name' => $line, 'description' => null, 'price' => null];
                continue;
            }

            if (str_starts_with($line, 'Precio:')) {
                $current['price'] = (float) str_replace(['Precio:', '$', '.', ' '], ['', '', '', ''], $line);
                if (strcasecmp($current['name'], $name) === 0) {
                    $foundData = $current;
                    break;
                }
                $current = null;
            } else {
                $current['description'] = $line;
            }
        }
    }

    if (!$foundData) {
        $this->warn('No encontré datos en el archivo para: ' . $name);
        return 0;
    }

    DB::beginTransaction();
    try {
        $product = DB::table('products')->where('name', $name)->first();
        if (!$product) {
            $this->warn('No existe el producto en BD, lo creo nuevo.');
            $productId = DB::table('products')->insertGetId([
                'name' => $foundData['name'],
                'description' => $foundData['description'] ?? '',
                'price' => $foundData['price'] ?? 0,
                'image' => null,
                'category' => 'Hamburguesas',
                'is_available' => 1,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $productId = $product->id;
            DB::table('products')->where('id', $productId)->update([
                'name' => $foundData['name'],
                'description' => $foundData['description'] ?? '',
                'price' => $foundData['price'] ?? 0,
                'updated_at' => now(),
            ]);
        }

        // Buscar imagen por coincidencia
        $imageFile = null;
        $dirFiles = @scandir($basePath) ?: [];
        $needle = strtolower($name);
        foreach ($dirFiles as $f) {
            if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'])) {
                $fn = strtolower(pathinfo($f, PATHINFO_FILENAME));
                if (str_contains($fn, str_replace(['-', '_'], [' ', ' '], $needle))) {
                    $imageFile = $f;
                    break;
                }
            }
        }

        if ($imageFile) {
            if (!is_dir($publicImagesPath))
                @mkdir($publicImagesPath, 0775, true);
            $ext = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));
            $base = Str::slug($name);
            $target = $base . '.' . $ext;
            @copy($basePath . DIRECTORY_SEPARATOR . $imageFile, $publicImagesPath . DIRECTORY_SEPARATOR . $target);
            DB::table('products')->where('id', $productId)->update(['image' => $target]);
        }

        // Limpiar variantes y opciones previas, recrear con estándar
        DB::table('product_variants')->where('product_id', $productId)->delete();
        DB::table('product_options')->where('product_id', $productId)->delete();

        // Tamaño
        foreach (['Simple', 'Doble', 'Triple'] as $idx => $size) {
            DB::table('product_variants')->insert([
                'product_id' => $productId,
                'name' => 'Tamaño',
                'value' => $size,
                'price_modifier' => 0,
                'is_available' => 1,
                'sort_order' => $idx,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Extras comunes del archivo
        // Reparse extras
        $extras = [];
        $lines = preg_split('/\r?\n/', $text);
        $section = null;
        foreach ($lines as $raw) {
            $line = trim($raw);
            if ($line === '')
                continue;
            if (str_starts_with($line, '---')) {
                $section = str_contains($line, 'EXTRAS') ? 'extras' : null;
                continue;
            }
            if ($section === 'extras' && str_contains($line, ':')) {
                [$n, $p] = array_map('trim', explode(':', $line, 2));
                $price = (float) str_replace(['$', '.', ' '], ['', '', ''], $p);
                $extras[] = ['name' => $n, 'price' => $price];
            }
        }
        foreach ($extras as $idx => $ex) {
            DB::table('product_options')->insert([
                'product_id' => $productId,
                'name' => 'Extras',
                'value' => $ex['name'],
                'price_modifier' => $ex['price'],
                'is_available' => 1,
                'sort_order' => $idx,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Aderezos sin costo
        $aderezos = [];
        $section = null;
        foreach ($lines as $raw) {
            $line = trim($raw);
            if ($line === '')
                continue;
            if (str_starts_with($line, '---')) {
                $section = str_contains($line, 'ADEREZOS') ? 'aderezos' : null;
                continue;
            }
            if ($section === 'aderezos') {
                $parts = array_filter(array_map('trim', explode(',', str_replace(['- '], [''], $line))));
                foreach ($parts as $p) {
                    if ($p !== '')
                        $aderezos[] = $p;
                }
            }
        }
        $aderezos = array_values(array_unique($aderezos));
        foreach ($aderezos as $idx => $opt) {
            DB::table('product_options')->insert([
                'product_id' => $productId,
                'name' => 'Aderezos',
                'value' => $opt,
                'price_modifier' => 0,
                'is_available' => 1,
                'sort_order' => $idx,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::commit();
        $this->info('Producto actualizado correctamente.');
        return 0;
    } catch (\Throwable $e) {
        DB::rollBack();
        $this->error('Error actualizando producto: ' . $e->getMessage());
        return 1;
    }
})->purpose('Actualizar un producto puntual desde el archivo de menú');
