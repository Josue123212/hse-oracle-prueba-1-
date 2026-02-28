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
        Schema::table('activities', function (Blueprint $table) {
            $table->boolean('es_plantilla')->default(false)->after('descripcion');
            $table->foreignId('parent_id')->nullable()->after('es_plantilla')->constrained('activities')->onDelete('cascade');
            $table->string('execution_period')->nullable()->after('parent_id'); // E.g., "Enero", "2026", "Semana 1"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'es_plantilla', 'execution_period']);
        });
    }
};
