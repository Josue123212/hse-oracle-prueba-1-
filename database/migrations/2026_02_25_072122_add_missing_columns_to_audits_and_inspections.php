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
        Schema::table('audits', function (Blueprint $table) {
            if (!Schema::hasColumn('audits', 'tipo_auditoria')) {
                $table->string('tipo_auditoria')->nullable();
            }
            if (!Schema::hasColumn('audits', 'entidad_auditora')) {
                $table->string('entidad_auditora')->nullable();
            }
            if (!Schema::hasColumn('audits', 'alcance')) {
                $table->string('alcance')->nullable();
            }
            if (!Schema::hasColumn('audits', 'auditores')) {
                $table->string('auditores')->nullable();
            }
        });

        Schema::table('inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('inspections', 'lugar')) {
                $table->string('lugar')->nullable();
            }
            if (!Schema::hasColumn('inspections', 'tipo_inspeccion')) {
                $table->string('tipo_inspeccion')->nullable();
            }
            if (!Schema::hasColumn('inspections', 'frecuencia')) {
                $table->string('frecuencia')->nullable();
            }
            if (!Schema::hasColumn('inspections', 'resultado')) {
                $table->text('resultado')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropColumn(['tipo_auditoria', 'entidad_auditora', 'alcance', 'auditores']);
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn(['lugar', 'tipo_inspeccion', 'frecuencia', 'resultado']);
        });
    }
};
