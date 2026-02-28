<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desactivar restricciones de clave foránea
        Schema::disableForeignKeyConstraints();

        // Lista de tablas a limpiar (MANTENIENDO users, roles, migrations, etc.)
        $tables = [
            'programs',
            'activities',
            'committees',
            'documentations',
            'drills',
            'incidents',
            'operational_controls',
            'promotions',
            'trainings',
            'inspections',
            'audits',
            'training_attendances',
            'training_materials',
            'program_statistics',
            'messages',
            'notifications',
            // Agrega aquí otras tablas que desees limpiar si existen
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->command->info("Tabla '$table' vaciada.");
            }
        }

        // Reactivar restricciones de clave foránea
        Schema::enableForeignKeyConstraints();

        $this->command->info('--- Limpieza completada. Usuarios y Roles intactos. ---');
    }
}
