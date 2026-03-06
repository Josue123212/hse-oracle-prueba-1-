<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'activities',
    'activity_executions',
    'inspections',
    'audits',
    'trainings',
    'drills', // simulacros
    'incidents',
    'documentations',
    'promotions',
    'operational_controls', // control_operacional
    'committees' // comite
];

echo "Checking table counts...\n";
foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        $count = DB::table($table)->count();
        echo str_pad($table, 25) . ": $count\n";
    } else {
        echo str_pad($table, 25) . ": Table not found\n";
    }
}
