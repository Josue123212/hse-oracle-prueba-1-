<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'audits',
            'inspections',
            'trainings',
            'drills',
            'incidents',
            'committees',
            'documentations',
            'promotions',
            'operational_controls',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'frecuencia')) {
                        $table->string('frecuencia')->nullable();
                    }
                    if (!Schema::hasColumn($tableName, 'veces_al_anio')) {
                        $table->integer('veces_al_anio')->default(1);
                    }
                    if (!Schema::hasColumn($tableName, 'ejecuciones_realizadas')) {
                        $table->integer('ejecuciones_realizadas')->default(0);
                    }
                    if (!Schema::hasColumn($tableName, 'detalle_frecuencia')) {
                        $table->string('detalle_frecuencia')->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'audits',
            'inspections',
            'trainings',
            'drills',
            'incidents',
            'committees',
            'documentations',
            'promotions',
            'operational_controls',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Only drop if we added them. Hard to track exactly, but safe to try drop if exists.
                    // However, 'inspections' had 'frecuencia' before.
                    // To be safe, we only drop the ones we likely added or all of them if rollback is requested.
                    // But for 'inspections', dropping 'frecuencia' might break the previous migration.
                    // Given this is a dev environment and user requested "add columns", I will define down to drop them EXCEPT frequency on inspections if I want to be super precise.
                    // But typically down() just reverses up().
                    
                    $columns = ['veces_al_anio', 'ejecuciones_realizadas', 'detalle_frecuencia'];
                    if ($tableName !== 'inspections') {
                        $columns[] = 'frecuencia';
                    }
                    
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
