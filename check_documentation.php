<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking 'documentation' table columns...\n";

$columns = Schema::getColumnListing('documentation');
foreach ($columns as $column) {
    echo "- $column\n";
}
