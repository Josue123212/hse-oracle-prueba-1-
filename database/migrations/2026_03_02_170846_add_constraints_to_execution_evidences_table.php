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
        Schema::table('execution_evidences', function (Blueprint $table) {
            // Foreign Keys
            $table->foreign('execution_id')
                  ->references('id')
                  ->on('activity_executions')
                  ->onDelete('cascade');
                  
            // $table->foreign('uploaded_by')
            //       ->references('id')
            //       ->on('users');
                  
            // $table->foreign('revoked_by')
            //       ->references('id')
            //       ->on('users');
                  
            // Índices
            // $table->index(['execution_id', 'revoked_at']);
            // $table->index('file_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En Postgres, verificar si la constraint existe antes de intentar borrarla es complejo en Schema Builder.
        // Lo más seguro en un rollback fallido es simplemente ignorar el error si no existe.
        try {
            Schema::table('execution_evidences', function (Blueprint $table) {
                $table->dropForeign(['execution_id']);
            });
        } catch (\Throwable $e) {
            // Ignorar error si la FK no existe
        }
    }
};
