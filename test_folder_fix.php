<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Log::info('--- INICIO PRUEBA CARPETA CORRECTA ---');
    $filename = 'prueba_final_carpeta_correcta_' . time() . '.txt';
    Storage::disk('google')->put($filename, 'Este archivo debe estar DENTRO de HSE-STORAGE.');
    Log::info("Archivo subido: {$filename}");
    
    if (Storage::disk('google')->exists($filename)) {
        Log::info("El disco confirma que el archivo existe.");
    } else {
        Log::error("El disco dice que el archivo NO existe.");
    }
    
    Log::info('--- FIN PRUEBA CARPETA CORRECTA ---');
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    Log::error($e->getMessage());
}
