<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$table = 'incidents';
$constraint = 'incidents_severidad_check';

echo "Checking constraint '$constraint' on table '$table'...\n";

$result = DB::select("
    SELECT pg_get_constraintdef(oid) AS constraint_def
    FROM pg_constraint
    WHERE conname = '$constraint'
");

if (!empty($result)) {
    echo "Definition: " . $result[0]->constraint_def . "\n";
} else {
    echo "Constraint not found.\n";
}
