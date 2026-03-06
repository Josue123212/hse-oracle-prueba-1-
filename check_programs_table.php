<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking 'programs' table columns...\n";

$columns = Schema::getColumnListing('programs');
foreach ($columns as $column) {
    echo "- $column\n";
}

echo "\nChecking 'program_components' table columns...\n";
$columns = Schema::getColumnListing('program_components');
foreach ($columns as $column) {
    echo "- $column\n";
}
