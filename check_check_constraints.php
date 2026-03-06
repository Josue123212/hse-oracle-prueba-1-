<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking CHECK constraints on 'programs' table...\n";

$results = DB::select("
    SELECT conname, pg_get_constraintdef(oid) as def
    FROM pg_constraint
    WHERE conrelid = 'programs'::regclass AND contype = 'c';
");

foreach ($results as $row) {
    echo "Constraint: {$row->conname}\n";
    echo "Definition: {$row->def}\n";
}
