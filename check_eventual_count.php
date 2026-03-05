<?php
use App\Models\Activity;

echo "Total Activities with frecuencia='eventual': " . Activity::where('frecuencia', 'eventual')->count() . "\n";
echo "Total Activities with frecuencia='eventual' grouped by tipo:\n";
foreach (Activity::where('frecuencia', 'eventual')->groupBy('tipo')->selectRaw('tipo, count(*) as count')->get() as $row) {
    echo "- " . $row->tipo . ": " . $row->count . "\n";
}
