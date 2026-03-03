<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * List of tables to update.
     * 'inspections' is already done in a previous migration.
     */
    protected $tables = [
        'audits',
        'trainings',
        'drills',
        'incidents',
        'committees',
        'documentations',
        'promotions',
        'operational_controls',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            // Check if table exists and has activity_id column
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'activity_id')) {
                continue;
            }

            // 1. Eliminar registros huérfanos (activity_id NULL)
            DB::table($tableName)
                ->whereNull('activity_id')
                ->delete();

            // 2. Eliminar registros que apuntan a actividades inexistentes
            DB::statement("DELETE FROM {$tableName} WHERE activity_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM activities WHERE activities.id = {$tableName}.activity_id)");

            // 3. Eliminar duplicados: si hay múltiples registros para la misma actividad, dejar solo la más reciente (mayor ID)
            // Postgres syntax
            DB::statement("
                DELETE FROM {$tableName} a USING {$tableName} b
                WHERE a.id < b.id 
                AND a.activity_id = b.activity_id
            ");

            // 4. Aplicar restricciones
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('activity_id')->nullable(false)->change();
                $table->unique('activity_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'activity_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                // Check if unique constraint exists before dropping (optional but good practice)
                // For simplicity in down method we just attempt to drop it
                try {
                    $table->dropUnique(['activity_id']);
                } catch (\Exception $e) {}

                $table->unsignedBigInteger('activity_id')->nullable()->change();
            });
        }
    }
};
