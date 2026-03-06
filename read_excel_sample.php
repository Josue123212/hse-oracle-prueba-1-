<?php

require __DIR__ . '/vendor/autoload.php';

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filePath = 'e:\pasantias_avance\hse-oracle-prueba-1-\planteamiento\QHSE-HSE-PROG-01 PROGRAMA ANUAL QHSE 2026 - ECYTEL S.A.C (1).xlsx';

if (!file_exists($filePath)) {
    die("File not found: $filePath\n");
}

echo "Reading Excel file...\n";

// Use a simple import class to get data
class DataImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Limit to first 20 rows for inspection
        $rows->take(20)->each(function ($row, $index) {
            echo "Row $index: " . $row->implode("\t") . "\n";
        });
    }
}

Excel::import(new DataImport, $filePath);
