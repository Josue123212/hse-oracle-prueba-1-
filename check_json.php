<?php

use App\Models\ActivityExecution;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = 491; // El ID que salió en el audit tool
$exec = ActivityExecution::find($id);

echo "JSON 'evidencia' actual:\n";
var_dump($exec->evidencia);

echo "\nRegistros en execution_evidences:\n";
$evidences = \App\Models\ExecutionEvidence::where('execution_id', $id)->get();
foreach ($evidences as $ev) {
    echo "- ID: {$ev->id}, Path: {$ev->file_path}, Revoked: " . ($ev->revoked_at ? 'YES' : 'NO') . "\n";
}
