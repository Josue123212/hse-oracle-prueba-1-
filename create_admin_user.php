<?php
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Verificar si existe la tabla roles
    $hasRolesTable = \Illuminate\Support\Facades\Schema::hasTable('roles');
    
    $roleId = null;

    if ($hasRolesTable) {
        // Intentar obtener un rol existente o crear uno
        $role = DB::table('roles')->where('name', 'Admin')->orWhere('name', 'admin')->first();
        
        if (!$role) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'Admin',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "Rol 'Admin' creado con ID: $roleId\n";
        } else {
            $roleId = $role->id;
            echo "Usando rol existente ID: $roleId\n";
        }
    }

    $userData = [
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
    ];

    if ($roleId) {
        $userData['role_id'] = $roleId;
    }

    $user = User::create($userData);
    
    echo "Usuario creado exitosamente.\n";
    echo "Email: admin@test.com\n";
    echo "Password: password\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
