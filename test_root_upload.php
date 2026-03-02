<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Log::info('--- INICIO PRUEBA RAIZ ---');
    $filename = 'prueba_raiz_' . time() . '.txt';
    Storage::disk('google')->put($filename, 'Contenido de prueba en la raíz');
    Log::info("Archivo subido: {$filename}");
    
    // Verificar si existe usando el disco
    if (Storage::disk('google')->exists($filename)) {
        Log::info("El disco dice que el archivo existe.");
    } else {
        Log::error("El disco dice que el archivo NO existe.");
    }
    
    Log::info('--- FIN PRUEBA RAIZ ---');
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    Log::error($e->getMessage());
}
