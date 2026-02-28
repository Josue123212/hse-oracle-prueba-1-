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
        // Drop the enum check constraint
        try {
            DB::statement("ALTER TABLE documentations DROP CONSTRAINT IF EXISTS documentations_estado_check");
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist
        }

        // Change column type to string (varchar)
        Schema::table('documentations', function (Blueprint $table) {
            $table->string('estado')->default('borrador')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create the enum check constraint (optional, but good practice)
        // Note: This assumes the original enum values
        DB::statement("ALTER TABLE documentations ADD CONSTRAINT documentations_estado_check CHECK (estado IN ('borrador', 'revision', 'aprobado', 'obsoleto'))");
    }
};
