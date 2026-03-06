<?php

require __DIR__ . '/vendor/autoload.php';

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filePath = 'e:\pasantias_avance\hse-oracle-prueba-1-\planteamiento\QHSE-HSE-PROG-01 PROGRAMA ANUAL QHSE 2026 - ECYTEL S.A.C (1).xlsx';

class ColumnInspector implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Row 8 is header (0-based index 8)
        $header = $rows[8];
        echo "Header (Row 8):\n";
        foreach ($header as $index => $value) {
            echo "[$index] $value\n";
        }

        // Row 11 is first data row
        $data = $rows[11];
        echo "\nData (Row 11):\n";
        foreach ($data as $index => $value) {
            echo "[$index] $value\n";
        }
    }
}

Excel::import(new ColumnInspector, $filePath);
