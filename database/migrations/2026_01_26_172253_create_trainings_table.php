<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')
                ->constrained('programs')
                ->onDelete('cascade');
            $table->string('tema', 255);
            $table->text('descripcion')->nullable();
            $table->date('fecha_programada');
            $table->time('hora_inicio')->nullable();
            $table->decimal('duracion_horas', 4, 2)->default(1.0);
            $table->enum('estado', ['programado', 'ejecutado', 'cancelado', 'reprogramado'])->default('programado');
            $table->integer('asistentes_esperados')->default(0);
            $table->integer('asistentes_reales')->default(0);
            $table->foreignId('responsable_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};