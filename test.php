<?php

/**
 * Script de prueba simple
 * URL: https://tcocina.org/test.php
 */
echo '<h1>✅ Script Funcionando!</h1>';
echo '<p>Si ves este mensaje, el servidor puede ejecutar archivos PHP en la raíz.</p>';

echo '<h2>📁 Información del Servidor</h2>';
echo '<p><strong>Directorio actual:</strong> ' . __DIR__ . '</p>';
echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';
echo '<p><strong>Servidor:</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</p>';

echo '<h2>📂 Estructura de Directorios</h2>';
$directories = [
    'storage/app/public' => __DIR__ . '/storage/app/public',
    'storage/app/public/products' => __DIR__ . '/storage/app/public/products',
    'public' => __DIR__ . '/public',
    'public/storage' => __DIR__ . '/public/storage',
    'public/storage/products' => __DIR__ . '/public/storage/products',
];

foreach ($directories as $name => $path) {
    $exists = is_dir($path);
    $color = $exists ? 'green' : 'red';
    $icon = $exists ? '✅' : '❌';
    echo "<p style='color:$color'>$icon $name: " . ($exists ? 'EXISTE' : 'NO EXISTE') . '</p>';
}

echo '<h2>🔧 Próximos Pasos</h2>';
echo '<p>Si este script funciona, entonces podemos ejecutar el script de imágenes.</p>';
echo '<p><a href="/solucionar-imagenes.php">Ejecutar Script de Imágenes</a></p>';
?>
