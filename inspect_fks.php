<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'documentations',
    'committees',
    'inspections',
    'audits',
    'trainings',
    'drills',
    'incidents',
    'promotions',
    'operational_controls'
];

foreach ($tables as $table) {
    echo "Foreign Keys for table: $table\n";
    $fks = DB::select("
        SELECT
            tc.constraint_name, 
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
        WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_name='$table';
    ");
    
    foreach ($fks as $fk) {
        echo "  - Column: {$fk->column_name} -> References: {$fk->foreign_table_name}.{$fk->foreign_column_name}\n";
    }
    echo "\n";
}
