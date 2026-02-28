<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->string('titulo');
            $table->string('tipo_documento'); // Procedimiento, Formato, Política, etc.
            $table->text('descripcion')->nullable();
            $table->string('version')->default('1.0');
            $table->enum('estado', ['borrador', 'revision', 'aprobado', 'obsoleto'])->default('borrador');
            $table->string('archivo_path')->nullable();
            $table->date('fecha_aprobacion')->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentations');
    }
};
