<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Roles:\n";
print_r(App\Models\Role::all()->toArray());
echo "\nUsers:\n";
print_r(App\Models\User::all()->toArray());
