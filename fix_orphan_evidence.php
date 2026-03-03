<?php

use App\Models\ExecutionEvidence;
use App\Services\EvidenceStorageService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧹 Limpiando evidencias huérfanas...\n";

// Buscar la última evidencia activa
$evidence = ExecutionEvidence::latest()->first();

if (!$evidence) {
    echo "No hay evidencias.\n";
    exit;
}

echo "🔍 Última evidencia: {$evidence->file_name} (ID: {$evidence->id})\n";
echo "📍 Estado actual: " . ($evidence->revoked_at ? "REVOCADO" : "ACTIVO") . "\n";

if (!$evidence->revoked_at) {
    echo "⚠️  Esta evidencia está ACTIVA en BD, pero el usuario reporta haberla borrado del formulario.\n";
    echo "🛠️  Revocando manualmente para corregir estado...\n";

    app(EvidenceStorageService::class)->revokeEvidence(
        $evidence, 
        1, // ID admin o sistema
        "Manual cleanup: User deleted from frontend but revocation failed initially."
    );

    echo "✅ Evidencia marcada como REVOCADA correctamente.\n";
} else {
    echo "ℹ️  La evidencia ya está revocada. No se requiere acción.\n";
}
