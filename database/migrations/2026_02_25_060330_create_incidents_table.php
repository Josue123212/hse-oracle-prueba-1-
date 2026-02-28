<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_ocurrencia');
            $table->string('lugar')->nullable();
            $table->enum('severidad', ['leve', 'moderado', 'grave', 'critico'])->default('leve');
            $table->enum('estado', ['abierto', 'investigacion', 'cerrado'])->default('abierto');
            $table->text('acciones_inmediatas')->nullable();
            $table->text('causa_raiz')->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
