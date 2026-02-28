<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->string('nombre_proceso');
            $table->string('parametro'); // Ej: Temperatura, Presión, Ruido
            $table->string('valor_esperado'); // Ej: < 85 dB
            $table->string('valor_medido')->nullable();
            $table->date('fecha_control');
            $table->enum('estado', ['conforme', 'no_conforme', 'pendiente'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_controls');
    }
};
