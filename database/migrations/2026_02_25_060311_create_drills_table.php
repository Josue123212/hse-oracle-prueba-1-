<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->date('fecha_programada');
            $table->date('fecha_ejecucion')->nullable();
            $table->enum('estado', ['programado', 'ejecutado', 'cancelado'])->default('programado');
            $table->string('escenario')->nullable();
            $table->text('evaluacion')->nullable();
            $table->integer('participantes_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drills');
    }
};
