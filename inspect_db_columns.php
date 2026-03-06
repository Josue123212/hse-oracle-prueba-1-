<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'documentations',
    'committees', // Asumiendo plural
    'inspections',
    'audits',
    'trainings',
    'drills',
    'incidents',
    'promotions',
    'operational_controls'
];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "Table: $table\n";
        print_r(Schema::getColumnListing($table));
        echo "\n";
    } else {
        echo "Table $table not found.\n";
    }
}
