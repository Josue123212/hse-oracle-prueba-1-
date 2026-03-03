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
        // Add SoftDeletes to activity_executions
        if (!Schema::hasColumn('activity_executions', 'deleted_at')) {
            Schema::table('activity_executions', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add SoftDeletes and deleted_by to execution_evidences
        if (!Schema::hasColumn('execution_evidences', 'deleted_at')) {
            Schema::table('execution_evidences', function (Blueprint $table) {
                $table->softDeletes();
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        // Add WORM Trigger for PostgreSQL
        DB::unprepared('
            CREATE OR REPLACE FUNCTION prevent_evidence_tampering_func() RETURNS TRIGGER AS $$
            BEGIN
                IF (OLD.file_hash IS DISTINCT FROM NEW.file_hash OR OLD.file_path IS DISTINCT FROM NEW.file_path) THEN
                    RAISE EXCEPTION \'WORM Violation: File content modification is forbidden.\';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER prevent_evidence_tampering
            BEFORE UPDATE ON execution_evidences
            FOR EACH ROW
            EXECUTE FUNCTION prevent_evidence_tampering_func();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop Trigger and Function
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_evidence_tampering ON execution_evidences');
        DB::unprepared('DROP FUNCTION IF EXISTS prevent_evidence_tampering_func');

        // Drop Columns
        if (Schema::hasColumn('execution_evidences', 'deleted_at')) {
            Schema::table('execution_evidences', function (Blueprint $table) {
                $table->dropForeign(['deleted_by']);
                $table->dropColumn(['deleted_by', 'deleted_at']);
            });
        }

        if (Schema::hasColumn('activity_executions', 'deleted_at')) {
            Schema::table('activity_executions', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
