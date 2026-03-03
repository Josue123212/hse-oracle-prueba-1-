<?php

use App\Models\ExecutionEvidence;
use App\Models\ActivityExecution;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Buscar la última ejecución tocada (editada o creada)
$latestExecution = ActivityExecution::orderBy('updated_at', 'desc')->first();

if (!$latestExecution) {
    echo "\n❌ No encontré ninguna ejecución de actividad en el sistema.\n";
    echo "Ve al frontend y crea o edita una ejecución primero.\n";
    exit;
}

echo "\n🔍 === AUDITORÍA FORENSE DE EVIDENCIAS (Evidence 2.0) === 🔍\n";
echo "Monitoreando Ejecución ID: {$latestExecution->id}\n";
echo "Actividad: " . ($latestExecution->activity->nombre ?? 'Sin nombre') . "\n";
echo "Última actualización: " . $latestExecution->updated_at->diffForHumans() . "\n";
echo str_repeat('=', 80) . "\n";

$evidences = ExecutionEvidence::where('execution_id', $latestExecution->id)
    ->orderBy('id', 'asc') // Orden cronológico
    ->get();

if ($evidences->isEmpty()) {
    echo "📂 ESTADO: Sin evidencias registradas en la nueva tabla 'execution_evidences'.\n";
    
    // Verificar si hay legacy en el JSON
    $legacy = $latestExecution->evidencia;
    if (!empty($legacy)) {
        echo "⚠️  AVISO: Hay archivos en el sistema antiguo (JSON) pero no en el nuevo (DB).\n";
        echo "   Esto es normal para datos viejos. Sube un archivo nuevo para probar.\n";
    }
} else {
    $mask = "| %-4s | %-25s | %-12s | %-15s | %-10s |\n";
    printf($mask, 'ID', 'ARCHIVO', 'ESTADO', 'HASH (SHA-256)', 'SUBIDO POR');
    echo str_repeat('-', 80) . "\n";
    
    foreach ($evidences as $ev) {
        $status = $ev->revoked_at ? "🔴 REVOCADO" : "🟢 ACTIVO";
        $hashShort = substr($ev->file_hash, 0, 8) . '...';
        $fileName = (strlen($ev->file_name) > 22) ? substr($ev->file_name, 0, 22) . '...' : $ev->file_name;
        $user = $ev->uploaded_by ?? 'N/A';
        
        printf($mask, $ev->id, $fileName, $status, $hashShort, $user);
        
        if ($ev->revoked_at) {
            echo "       ↳ 🕒 Fecha Revocación: {$ev->revoked_at}\n";
            echo "       ↳ 📝 Motivo: {$ev->revoke_reason}\n";
        }
    }
}
echo str_repeat('=', 80) . "\n\n";
