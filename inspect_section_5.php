<?php

require __DIR__ . '/vendor/autoload.php';

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filePath = 'e:\pasantias_avance\hse-oracle-prueba-1-\planteamiento\QHSE-HSE-PROG-01 PROGRAMA ANUAL QHSE 2026 - ECYTEL S.A.C (1).xlsx';

class SectionInspector implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Dump rows 45 to 60
        $rows->slice(45, 20)->each(function ($row, $index) {
            echo "Row $index: " . $row[2] . " | " . $row[3] . "\n";
        });
    }
}

Excel::import(new SectionInspector, $filePath);
