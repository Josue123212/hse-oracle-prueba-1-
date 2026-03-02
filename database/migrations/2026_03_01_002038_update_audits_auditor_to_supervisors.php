<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First set auditor_id to null because the ID reference changes from users to supervisors
        DB::table('audits')->update(['auditor_id' => null]);

        Schema::table('audits', function (Blueprint $table) {
            if (Schema::hasColumn('audits', 'auditor_id')) {
                try {
                    $table->dropForeign(['auditor_id']);
                } catch (\Exception $e) {}

                $table->foreign('auditor_id')
                      ->references('id')
                      ->on('supervisors')
                      ->onDelete('set null');
            } else {
                $table->foreignId('auditor_id')
                      ->nullable()
                      ->constrained('supervisors')
                      ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            try {
                $table->dropForeign(['auditor_id']);
            } catch (\Exception $e) {}

            $table->foreign('auditor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
