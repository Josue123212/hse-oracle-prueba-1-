<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear los roles básicos
        $admin = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'encargado']);
        Role::firstOrCreate(['name' => 'lector']);
        Role::firstOrCreate(['name' => 'alumno']);

        // 2. Crear tu usuario administrador vinculado al rol
        User::firstOrCreate(
            ['email' => 'oracle.test@test.com'],
            [
                'name' => 'ORACLE PERU SAC',
                'password' => Hash::make('dev123'),
                'role_id' => $admin->id,
            ]
        );
    }
}
