<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->string('nombre'); // Ej: COPASST, Comité de Convivencia
            $table->string('tema_principal')->nullable();
            $table->date('fecha_programada');
            $table->date('fecha_realizada')->nullable();
            $table->enum('estado', ['programado', 'realizado', 'cancelado'])->default('programado');
            $table->text('acuerdos')->nullable();
            $table->string('acta_path')->nullable(); // Archivo
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committees');
    }
};
