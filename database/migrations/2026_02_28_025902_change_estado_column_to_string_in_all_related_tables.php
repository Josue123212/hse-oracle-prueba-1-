<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'incidents',
            'committees',
            'trainings',
            'drills',
            'inspections',
            'audits',
            'promotions',
            'operational_controls',
        ];

        foreach ($tables as $table) {
            // Drop check constraint if exists
            try {
                // Drop constraint using raw SQL
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_estado_check");
            } catch (\Exception $e) {
                // Ignore
            }

            // Change column type to string
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->string('estado')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy rollback for multiple tables with potentially different enum values
        // We leave them as strings.
    }
};
