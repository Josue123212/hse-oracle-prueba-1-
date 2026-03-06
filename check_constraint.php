<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking 'responsable_id' constraint on 'activities' table...\n";

// Get foreign keys for activities table
// This query is for MySQL/MariaDB
$fks = DB::select("
    SELECT 
        TABLE_NAME, 
        COLUMN_NAME, 
        CONSTRAINT_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME
    FROM
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
        REFERENCED_TABLE_SCHEMA = '" . env('DB_DATABASE') . "' AND
        TABLE_NAME = 'activities' AND
        COLUMN_NAME = 'responsable_id';
");

foreach ($fks as $fk) {
    echo "Constraint: {$fk->CONSTRAINT_NAME}\n";
    echo "Referenced Table: {$fk->REFERENCED_TABLE_NAME}\n";
    echo "Referenced Column: {$fk->REFERENCED_COLUMN_NAME}\n";
}

echo "\nChecking 'positions' table existence...\n";
if (Schema::hasTable('positions')) {
    echo "Table 'positions' exists.\n";
} else {
    echo "Table 'positions' DOES NOT exist.\n";
}
