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
        Schema::create('activity_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->date('fecha_programada');
            $table->date('fecha_ejecucion_real')->nullable();
            $table->string('estado')->default('pendiente'); // pendiente, ejecutado, no_cumplio, vencido
            $table->text('observacion')->nullable();
            $table->string('evidencia')->nullable(); // Path to file
            $table->json('data')->nullable(); // For extra fields (e.g. hallazgos count, assistants, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_executions');
    }
};
