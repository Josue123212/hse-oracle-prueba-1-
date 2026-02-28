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
        // Fix foreign key constraints to cascade delete on activity deletion
        
        Schema::table('audits', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->foreign('activity_id')
                ->references('id')
                ->on('activities')
                ->onDelete('cascade');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->foreign('activity_id')
                ->references('id')
                ->on('activities')
                ->onDelete('cascade');
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->foreign('activity_id')
                ->references('id')
                ->on('activities')
                ->onDelete('cascade');
        });
        
        // Add other tables if necessary (drills, operational_controls, etc.) if they have activity_id
        // Checking existing migrations, operational_controls has activity_id
        
        if (Schema::hasTable('operational_controls')) {
            Schema::table('operational_controls', function (Blueprint $table) {
                // Check if column exists first to be safe, though migration order implies it does
                if (Schema::hasColumn('operational_controls', 'activity_id')) {
                    // Try to drop foreign key if it exists. 
                    // Note: FK name is usually operational_controls_activity_id_foreign
                    // We use array syntax which Laravel converts to that name
                    $table->dropForeign(['activity_id']);
                    
                    $table->foreign('activity_id')
                        ->references('id')
                        ->on('activities')
                        ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to nullOnDelete (previous state)
        
        Schema::table('audits', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->foreign('activity_id')
                ->references('id')
                ->on('activities')
                ->nullOnDelete();
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->foreign('activity_id')
                ->references('id')
                ->on('activities')
                ->nullOnDelete();
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->foreign('activity_id')
                ->references('id')
                ->on('activities')
                ->nullOnDelete();
        });

        if (Schema::hasTable('operational_controls')) {
             Schema::table('operational_controls', function (Blueprint $table) {
                if (Schema::hasColumn('operational_controls', 'activity_id')) {
                    $table->dropForeign(['activity_id']);
                    $table->foreign('activity_id')
                        ->references('id')
                        ->on('activities')
                        ->nullOnDelete();
                }
            });
        }
    }
};
