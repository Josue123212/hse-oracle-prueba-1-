<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->string('nombre_campana');
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['planificado', 'en_curso', 'finalizado', 'cancelado'])->default('planificado');
            $table->string('publico_objetivo')->nullable(); // Ej: Operarios, Administrativos, Todos
            $table->string('material_entregado')->nullable(); // Ej: Folletos, EPP, Merchandising
            $table->integer('participantes_estimados')->default(0);
            $table->integer('participantes_reales')->default(0);
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
