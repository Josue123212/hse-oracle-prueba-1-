<?php

use App\Models\Activity;
use App\Models\Program;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Probando relación Activity -> Program...\n";

// Buscar una actividad con program_id
$activity = Activity::whereNotNull('program_id')->first();

if (!$activity) {
    echo "⚠️ No hay actividades con programa asignado para probar.\n";
    exit;
}

echo "✅ Actividad encontrada: ID {$activity->id} - {$activity->nombre}\n";
echo "🔗 Program ID: {$activity->program_id}\n";

try {
    $program = $activity->program;
    if ($program) {
        echo "✅ Relación FUNCIONA: {$program->nombre} (ID: {$program->id})\n";
    } else {
        echo "❌ La relación devuelve NULL (pero el ID existe).\n";
        // Intentar ver si el método existe
        if (method_exists($activity, 'program')) {
            echo "ℹ️ El método 'program' existe en el modelo.\n";
        } else {
            echo "❌ El método 'program' NO existe en el modelo Activity.\n";
        }
    }
} catch (\Exception $e) {
    echo "💥 Error al acceder a la relación: " . $e->getMessage() . "\n";
}
