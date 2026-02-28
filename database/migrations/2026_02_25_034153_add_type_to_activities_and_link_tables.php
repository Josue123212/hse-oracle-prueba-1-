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
            $table->string('tipo')->nullable()->after('program_id');
        });

        Schema::table('audits', function (Blueprint $table) {
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete()->after('id');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete()->after('id');
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('audits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('activity_id');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('activity_id');
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('activity_id');
        });
    }
};
