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
        $tables = [
            'drills',
            'incidents',
            'committees',
            'documentations',
            'promotions',
            'operational_controls',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Check if column exists before trying to modify it
                    if (Schema::hasColumn($tableName, 'responsable_id')) {
                        // Drop foreign key if it exists. 
                        // Note: The constraint name might be table_responsable_id_foreign or similar.
                        // We try to drop it using the column name array syntax which Laravel resolves.
                        try {
                            $table->dropForeign(['responsable_id']);
                        } catch (\Exception $e) {
                            // Ignore if foreign key doesn't exist or has a different name (unlikely with standard conventions)
                        }

                        // We might need to ensure the column is unsigned big integer if it wasn't
                        // But usually it is. We can just modify the foreign key constraint.
                        
                        // Add new foreign key pointing to 'positions'
                        $table->foreign('responsable_id')
                              ->references('id')
                              ->on('positions')
                              ->onDelete('set null');
                    } else {
                        // If column doesn't exist, add it
                        $table->foreignId('responsable_id')
                              ->nullable()
                              ->constrained('positions')
                              ->nullOnDelete();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'drills',
            'incidents',
            'committees',
            'documentations',
            'promotions',
            'operational_controls',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'responsable_id')) {
                Schema::table($table, function (Blueprint $table) {
                    try {
                        $table->dropForeign(['responsable_id']);
                    } catch (\Exception $e) {}

                    // Restore foreign key to users
                    $table->foreign('responsable_id')
                          ->references('id')
                          ->on('users')
                          ->onDelete('set null');
                });
            }
        }
    }
};
