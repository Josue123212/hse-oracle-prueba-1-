<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150); // Liderazgo y Compromiso, etc.
            $table->text('descripcion')->nullable();
            $table->integer('anio')->default(date('Y'));
            $table->enum('estado', ['borrador', 'aprobado', 'cerrado'])->default('borrador');
            $table->foreignId('supervisor_id')
                ->nullable()
                ->constrained('supervisors')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};