<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Operational Controls Columns:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('operational_controls'));

echo "\nTrainings Columns:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('trainings'));

echo "\nActivities Columns:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('activities'));
