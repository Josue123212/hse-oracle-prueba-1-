<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking 'responsable_id' constraint on 'activities' table (PostgreSQL)...\n";

$results = DB::select("
    SELECT
        tc.constraint_name, 
        tc.table_name, 
        kcu.column_name, 
        ccu.table_name AS foreign_table_name,
        ccu.column_name AS foreign_column_name 
    FROM 
        information_schema.table_constraints AS tc 
        JOIN information_schema.key_column_usage AS kcu
          ON tc.constraint_name = kcu.constraint_name
          AND tc.table_schema = kcu.table_schema
        JOIN information_schema.constraint_column_usage AS ccu
          ON ccu.constraint_name = tc.constraint_name
          AND ccu.table_schema = tc.table_schema
    WHERE tc.constraint_type = 'CHECK' AND tc.table_name='programs';
");

if (empty($results)) {
    echo "No FK constraint found for 'responsable_id'.\n";
} else {
    foreach ($results as $row) {
        echo "Constraint: {$row->constraint_name}\n";
        echo "Table: {$row->table_name}\n";
        echo "Column: {$row->column_name}\n";
        echo "Foreign Table: {$row->foreign_table_name}\n";
        echo "Foreign Column: {$row->foreign_column_name}\n";
    }
}
