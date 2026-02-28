<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->enum('frecuencia', ['diario', 'semanal', 'mensual', 'trimestral', 'semestral', 'anual', 'unico'])->default('unico');
            $table->integer('meta')->default(100)->comment('Meta numérica o porcentual');
            $table->string('unidad_medida', 50)->default('%');
            $table->boolean('es_obligatoria')->default(true);
            $table->foreignId('responsable_id')->nullable()->constrained('users')->onDelete('set null'); // Quien debe ejecutarla
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};