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
            'activities',
            'inspections',
            'audits',
            'drills',
            'trainings',
            'committees',
            'documentations',
            'promotions',
            'operational_controls',
            'incidents'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'estado')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('estado');
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
            'activities',
            'inspections',
            'audits',
            'drills',
            'trainings',
            'committees',
            'documentations',
            'promotions',
            'operational_controls',
            'incidents'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'estado')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('estado')->default('pendiente');
                });
            }
        }
    }
};
