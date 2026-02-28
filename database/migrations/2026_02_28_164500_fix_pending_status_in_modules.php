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
            'activities',
            'inspections',
            'audits',
            'trainings',
            'drills',
            'incidents',
            'activity_executions' // Just to be sure
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'estado')) {
                DB::table($table)
                    ->where('estado', 'pendiente')
                    ->update(['estado' => 'programado']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse
    }
};
