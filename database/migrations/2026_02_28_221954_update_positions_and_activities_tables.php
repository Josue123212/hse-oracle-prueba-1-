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
        // 1. Añadir campo 'firma' a la tabla 'positions'
        Schema::table('positions', function (Blueprint $table) {
            $table->longText('firma')->nullable()->after('descripcion');
        });

        // 2. Modificar claves foráneas en la tabla 'activities'
        Schema::table('activities', function (Blueprint $table) {
            // Eliminar claves foráneas existentes (apuntan a 'users')
            // Se usa array para que Laravel infiera el nombre: activities_responsable_id_foreign
            $table->dropForeign(['responsable_id']);
            $table->dropForeign(['responsable_delegado_id']);

            // Añadir nuevas claves foráneas (apuntan a 'positions')
            $table->foreign('responsable_id')
                  ->references('id')
                  ->on('positions')
                  ->onDelete('set null'); // O restrict/cascade según necesidad, set null es seguro

            $table->foreign('responsable_delegado_id')
                  ->references('id')
                  ->on('positions')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios en 'activities'
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            $table->dropForeign(['responsable_delegado_id']);

            // Restaurar FKs a 'users'
            $table->foreign('responsable_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->foreign('responsable_delegado_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // Revertir cambios en 'positions'
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('firma');
        });
    }
};
