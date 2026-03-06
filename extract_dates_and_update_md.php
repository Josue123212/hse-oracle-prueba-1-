<?php

require __DIR__ . '/vendor/autoload.php';

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$excelPath = 'e:\pasantias_avance\hse-oracle-prueba-1-\planteamiento\QHSE-HSE-PROG-01 PROGRAMA ANUAL QHSE 2026 - ECYTEL S.A.C (1).xlsx';
$mdPath = 'e:\pasantias_avance\hse-oracle-prueba-1-\planteamiento\rellenado_de_datos.md';

if (!file_exists($excelPath)) {
    die("Excel file not found.\n");
}
if (!file_exists($mdPath)) {
    die("MD file not found.\n");
}

echo "Reading Excel...\n";

class DateExtractor implements ToCollection
{
    public $activityDates = [];

    public function collection(Collection $rows)
    {
        // Iterate from row 9 (index 9) to skip header
        for ($i = 9; $i < $rows->count(); $i++) {
            $row = $rows[$i];
            
            // Col 2 is ID
            $id = isset($row[2]) ? trim($row[2]) : '';
            
            if (empty($id)) continue;
            
            // Check if ID looks like an activity ID (e.g., 1.1, A.1, etc.)
            // Some might be section headers like "1.- ..." or "A.- ..."
            // Real IDs seem to be "1.1", "A.1", "5.B.1" ?
            // Let's inspect "5.B.1" in previous output.
            // Row 54: B.1
            // So format is digit.digit or Letter.digit
            
            // Collect dates
            $dates = [];
            $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
            $colIndex = 10; // Jan P starts at 10
            
            foreach ($months as $m) {
                $val = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';
                if (strtoupper($val) === 'P') {
                    $dates[] = "2026-$m-01";
                }
                $colIndex += 2;
            }
            
            if (!empty($dates)) {
                $this->activityDates[$id] = implode(',', $dates);
            }
        }
    }
}

$extractor = new DateExtractor();
Excel::import($extractor, $excelPath);

echo "Found dates for " . count($extractor->activityDates) . " activities.\n";

// Debug: print some dates
// print_r(array_slice($extractor->activityDates, 0, 5));

echo "Updating MD file...\n";

$lines = file($mdPath);
$newLines = [];
$updatedCount = 0;

foreach ($lines as $line) {
    $trimLine = trim($line);
    
    // Check if line is a data line
    // Data lines structure: Component//ID//Meta...
    // Regex: ^(.*?)\/\/(.*?)\/\/
    
    if (preg_match('/^(.*?)\/\/(.*?)\/\//', $trimLine, $matches)) {
        $id = trim($matches[2]);
        
        // Lookup dates
        if (isset($extractor->activityDates[$id])) {
            $dates = $extractor->activityDates[$id];
            // Check if already has dates (avoid double append if run multiple times)
            // But user asked to add it. Assuming clean state or append.
            // If line already ends with a date-like string, maybe replace?
            // The instruction is "revisa... y añadelo".
            // Let's assume append to the end.
            
            // Remove newline char from original line
            $line = rtrim($line, "\r\n");
            $newLines[] = $line . "//" . $dates . "\n";
            $updatedCount++;
        } else {
            // ID not found in Excel map
            // Maybe ID mismatch?
            // Try matching without spaces?
            $newLines[] = $line; // Keep as is
            // echo "Warning: ID '$id' not found in Excel.\n";
        }
    } else {
        $newLines[] = $line;
    }
}

file_put_contents($mdPath, implode('', $newLines));

echo "Updated $updatedCount lines in MD file.\n";
