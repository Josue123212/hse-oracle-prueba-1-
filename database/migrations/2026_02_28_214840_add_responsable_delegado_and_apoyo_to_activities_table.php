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
            $table->foreignId('responsable_delegado_id')->nullable()->constrained('users')->onDelete('set null')->after('responsable_id');
            $table->string('apoyo')->nullable()->after('responsable_delegado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['responsable_delegado_id']);
            $table->dropColumn(['responsable_delegado_id', 'apoyo']);
        });
    }
};
