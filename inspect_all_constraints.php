<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['inspections', 'audits', 'incidents'];

foreach ($tables as $table) {
    echo "Constraints for table '$table':\n";
    $constraints = DB::select("
        SELECT conname, pg_get_constraintdef(oid) AS constraint_def
        FROM pg_constraint
        WHERE conrelid = '$table'::regclass
        AND contype = 'c'
    ");
    
    foreach ($constraints as $c) {
        echo "  - {$c->conname}: {$c->constraint_def}\n";
    }
    echo "\n";
}
