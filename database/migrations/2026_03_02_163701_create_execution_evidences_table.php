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
        // Paso 1: Crear tabla física SIN constraints, ni índices complejos, NI COMENTARIOS.
        Schema::create('execution_evidences', function (Blueprint $table) {
            $table->id();
            
            // Relación lógica (física bigint)
            $table->unsignedBigInteger('execution_id');
            
            // Metadata Física
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type')->nullable();
            
            // Integridad Forense
            $table->string('file_hash', 64);
            
            // Auditoría de Carga (física bigint)
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            
            // Auditoría de Revocación (física bigint)
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('revoked_by')->nullable();
            $table->text('revoke_reason')->nullable();
            
            // Flags
            $table->boolean('is_replacement')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('execution_evidences');
    }
};
