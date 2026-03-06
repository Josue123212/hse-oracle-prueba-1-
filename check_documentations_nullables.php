<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking 'documentations' table nullables...\n";

$columns = DB::select("
    SELECT column_name, is_nullable
    FROM information_schema.columns
    WHERE table_name = 'documentations' AND column_name IN ('version', 'responsable_id', 'descripcion', 'archivo_path');
");

foreach ($columns as $col) {
    echo "{$col->column_name}: Nullable = {$col->is_nullable}\n";
}
