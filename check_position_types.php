<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\PositionType;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking 'position_types' table content...\n";

if (Schema::hasTable('position_types')) {
    $types = DB::table('position_types')->get();
    if ($types->isEmpty()) {
        echo "Table 'position_types' is empty.\n";
    } else {
        foreach ($types as $type) {
            echo "ID: {$type->id}, Name: {$type->nombre}\n";
        }
    }
} else {
    echo "Table 'position_types' DOES NOT exist.\n";
}
