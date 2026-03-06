<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar que el rol admin existe
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // 2. Crear el usuario administrador
        $user = User::firstOrCreate(
            ['email' => 'oracle.test@test.com'],
            [
                'name' => 'ORACLE PERU SAC',
                'password' => Hash::make('dev123'),
                'role_id' => $adminRole->id,
            ]
        );

        $this->command->info('Usuario Administrador creado exitosamente.');
        $this->command->info('Email: oracle.test@test.com');
        $this->command->info('Password: dev123');
    }
}
