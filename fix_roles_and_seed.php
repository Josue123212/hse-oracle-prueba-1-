<?php
use App\Models\Role;
use Illuminate\Support\Facades\Artisan;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // 1. Fix 'Admin' -> 'admin'
    $role = Role::where('name', 'Admin')->first();
    if ($role) {
        $role->update(['name' => 'admin']);
        echo "Rol 'Admin' corregido a 'admin'.\n";
    } else {
        echo "No se encontró rol 'Admin' (tal vez ya es 'admin').\n";
    }

    // 2. Run DatabaseSeeder
    echo "Ejecutando db:seed...\n";
    Artisan::call('db:seed');
    echo "Seeders ejecutados correctamente.\n";
    echo Artisan::output();

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
