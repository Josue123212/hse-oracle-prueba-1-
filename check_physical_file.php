<?php

use App\Models\ExecutionEvidence;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Buscar la última evidencia registrada (la más reciente)
$evidence = ExecutionEvidence::latest('id')->first();

if (!$evidence) {
    echo "❌ No hay evidencias registradas en la base de datos.\n";
    exit;
}

echo "\n🔍 Verificando última evidencia (ID: {$evidence->id})\n";
echo "📂 Archivo: {$evidence->file_name}\n";
echo "📍 Path en BD: {$evidence->file_path}\n";
echo "User: " . ($evidence->uploaded_by ?? 'N/A') . "\n";
echo "Estado BD: " . ($evidence->revoked_at ? "🔴 REVOCADO ({$evidence->revoked_at})" : "🟢 ACTIVO") . "\n";

echo str_repeat('-', 40) . "\n";

try {
    $exists = Storage::disk('google')->exists($evidence->file_path);

    if ($exists) {
        echo "✅ El archivo FÍSICO existe en Google Drive.\n";
        echo "🔗 URL (si tienes permisos): " . Storage::disk('google')->url($evidence->file_path) . "\n";
        
        if ($evidence->revoked_at) {
            echo "🏆 ÉXITO WORM: El archivo está revocado en BD pero existe físicamente.\n";
        } else {
            echo "ℹ️  Normal: El archivo está activo y existe.\n";
        }
    } else {
        echo "❌ El archivo FÍSICO NO existe en Google Drive.\n";
        if ($evidence->revoked_at) {
             echo "⚠️  FALLO WORM: Está revocado, pero el archivo físico desapareció.\n";
        } else {
             echo "⚠️  CRÍTICO: Está activo en BD, pero no existe el archivo. (Subida fallida o borrado externo)\n";
        }
    }
} catch (\Exception $e) {
    echo "💥 Error al conectar con Drive: " . $e->getMessage() . "\n";
}
echo "\n";
