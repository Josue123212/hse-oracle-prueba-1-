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
        // 1. Eliminar inspecciones huérfanas (activity_id NULL)
        DB::table('inspections')
            ->whereNull('activity_id')
            ->delete();

        // 2. Eliminar inspecciones que apuntan a actividades inexistentes
        DB::statement('DELETE FROM inspections WHERE activity_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM activities WHERE activities.id = inspections.activity_id)');

        // 3. Eliminar duplicados: si hay múltiples inspecciones para la misma actividad, dejar solo la más reciente
        // Postgres syntax for deleting duplicates keeping the one with max id
        DB::statement('
            DELETE FROM inspections a USING inspections b
            WHERE a.id < b.id 
            AND a.activity_id = b.activity_id
        ');

        // 4. Aplicar restricciones
        Schema::table('inspections', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_id')->nullable(false)->change();
            $table->unique('activity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropUnique(['activity_id']);
            $table->unsignedBigInteger('activity_id')->nullable()->change();
        });
    }
};
