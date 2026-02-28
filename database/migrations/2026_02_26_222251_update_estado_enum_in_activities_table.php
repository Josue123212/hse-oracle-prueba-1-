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
        // Drop the existing check constraint
        DB::statement("ALTER TABLE activities DROP CONSTRAINT IF EXISTS activities_estado_check");

        // Add the new check constraint with 'en_proceso'
        DB::statement("ALTER TABLE activities ADD CONSTRAINT activities_estado_check CHECK (estado IN ('programado', 'ejecutado', 'no_cumplio', 'en_proceso'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original check constraint
        // Note: This might fail if there are rows with 'en_proceso' when rolling back.
        // Usually, one would update those rows first, but for simplicity here we just revert structure.
        DB::statement("ALTER TABLE activities DROP CONSTRAINT IF EXISTS activities_estado_check");
        DB::statement("ALTER TABLE activities ADD CONSTRAINT activities_estado_check CHECK (estado IN ('programado', 'ejecutado', 'no_cumplio'))");
    }
};
