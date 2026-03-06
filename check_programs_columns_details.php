<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking 'programs' table columns details...\n";

$columns = DB::select("
    SELECT column_name, is_nullable
    FROM information_schema.columns
    WHERE table_name = 'programs' AND column_name = 'supervisor_id';
");

foreach ($columns as $col) {
    echo "{$col->column_name}: Nullable = {$col->is_nullable}\n";
}
