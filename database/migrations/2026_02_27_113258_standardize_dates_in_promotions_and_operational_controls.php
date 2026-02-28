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
        Schema::table('promotions', function (Blueprint $table) {
            $table->renameColumn('fecha_inicio', 'fecha_programada');
            $table->dropColumn('fecha_fin');
        });

        Schema::table('operational_controls', function (Blueprint $table) {
            $table->renameColumn('fecha_control', 'fecha_programada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->renameColumn('fecha_programada', 'fecha_inicio');
            $table->date('fecha_fin')->nullable();
        });

        Schema::table('operational_controls', function (Blueprint $table) {
            $table->renameColumn('fecha_programada', 'fecha_control');
        });
    }
};
