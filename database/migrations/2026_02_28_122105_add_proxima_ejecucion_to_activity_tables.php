<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'activities',
        'inspections',
        'trainings',
        'audits',
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
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'proxima_ejecucion')) {
                    $table->date('proxima_ejecucion')->nullable()->after('fecha_fin'); // Assuming fecha_fin exists, otherwise adjust
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'proxima_ejecucion')) {
                    $table->dropColumn('proxima_ejecucion');
                }
            });
        }
    }
};
