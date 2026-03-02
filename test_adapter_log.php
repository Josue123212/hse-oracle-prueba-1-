<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Log::info('--- INICIO PRUEBA ADAPTER ---');
    // Esto forzará la creación del driver 'google' y ejecutará el callback en AppServiceProvider
    $exists = Storage::disk('google')->exists('test-file-log.txt');
    echo "Existe archivo? " . ($exists ? 'Sí' : 'No') . "\n";
    Log::info('--- FIN PRUEBA ADAPTER ---');
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    Log::error($e->getMessage());
}
