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
        // Update 'inspections' table
        if (Schema::hasTable('inspections')) {
            Schema::table('inspections', function (Blueprint $table) {
                // Drop existing foreign key pointing to 'users'
                $table->dropForeign(['responsable_id']);

                // Add new foreign key pointing to 'positions'
                $table->foreign('responsable_id')
                      ->references('id')
                      ->on('positions')
                      ->onDelete('set null');
            });
        }

        // Update 'trainings' table
        if (Schema::hasTable('trainings')) {
            Schema::table('trainings', function (Blueprint $table) {
                // Drop existing foreign key pointing to 'users'
                $table->dropForeign(['responsable_id']);

                // Add new foreign key pointing to 'positions'
                $table->foreign('responsable_id')
                      ->references('id')
                      ->on('positions')
                      ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert changes in 'inspections'
        if (Schema::hasTable('inspections')) {
            Schema::table('inspections', function (Blueprint $table) {
                $table->dropForeign(['responsable_id']);

                $table->foreign('responsable_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            });
        }

        // Revert changes in 'trainings'
        if (Schema::hasTable('trainings')) {
            Schema::table('trainings', function (Blueprint $table) {
                $table->dropForeign(['responsable_id']);

                $table->foreign('responsable_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            });
        }
    }
};
