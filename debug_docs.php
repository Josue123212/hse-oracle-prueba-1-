<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$docs = App\Models\Documentation::with('activity')->get();
foreach ($docs as $d) {
    echo "ID: " . $d->id . "\n";
    echo "Titulo: " . $d->titulo . "\n";
    echo "Estado: " . $d->estado . "\n";
    echo "Fecha Inicio Activity: " . ($d->activity ? $d->activity->fecha_inicio : 'No Activity') . "\n";
    echo "Frecuencia Activity: " . ($d->activity ? $d->activity->frecuencia : 'No Activity') . "\n";
    echo "------------------\n";
}
