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
        // 1. Agregar la columna nullable inicialmente
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('program_component_id')->nullable()->after('id')->constrained('program_components')->nullOnDelete();
        });

        // 2. Migrar datos existentes (si los hay)
        $activities = DB::table('activities')->whereNotNull('program_id')->get();

        foreach ($activities as $activity) {
            // Buscar o crear un componente "General" para el programa de la actividad
            $componentId = DB::table('program_components')
                ->where('program_id', $activity->program_id)
                ->where('name', 'General (Migrado)')
                ->value('id');

            if (!$componentId) {
                $componentId = DB::table('program_components')->insertGetId([
                    'program_id' => $activity->program_id,
                    'name' => 'General (Migrado)',
                    'type' => 'subprograma', // Tipo por defecto
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Asignar la actividad al componente
            DB::table('activities')
                ->where('id', $activity->id)
                ->update(['program_component_id' => $componentId]);
        }

        // 3. Eliminar la columna antigua program_id
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['program_id']); // Eliminar FK primero
            $table->dropColumn('program_id');    // Luego la columna
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->constrained('programs');
        });

        // Intentar restaurar datos (opcional, básico)
        $activities = DB::table('activities')->whereNotNull('program_component_id')->get();
        foreach ($activities as $activity) {
            $component = DB::table('program_components')->find($activity->program_component_id);
            if ($component) {
                DB::table('activities')
                    ->where('id', $activity->id)
                    ->update(['program_id' => $component->program_id]);
            }
        }

        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['program_component_id']);
            $table->dropColumn('program_component_id');
        });
    }
};
