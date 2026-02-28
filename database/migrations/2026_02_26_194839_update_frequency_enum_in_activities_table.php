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
        // 1. Drop the existing check constraint for 'frecuencia'
        // Note: Laravel default constraint name for enum is table_column_check
        DB::statement("ALTER TABLE activities DROP CONSTRAINT IF EXISTS activities_frecuencia_check");

        // 2. Add the detail column
        Schema::table('activities', function (Blueprint $table) {
            $table->string('detalle_frecuencia')->nullable()->after('frecuencia')->comment('Razón o detalle de la frecuencia eventual');
        });

        // 3. Migrate existing 'unico' records to 'eventual'
        DB::table('activities')->where('frecuencia', 'unico')->update(['frecuencia' => 'eventual']);

        // 4. Add the new check constraint with 'eventual' and without 'unico'
        DB::statement("ALTER TABLE activities ADD CONSTRAINT activities_frecuencia_check CHECK (frecuencia IN ('diario', 'semanal', 'mensual', 'trimestral', 'semestral', 'anual', 'eventual'))");
        
        // Set default value to 'eventual'
        DB::statement("ALTER TABLE activities ALTER COLUMN frecuencia SET DEFAULT 'eventual'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop the new check constraint
        DB::statement("ALTER TABLE activities DROP CONSTRAINT IF EXISTS activities_frecuencia_check");

        // 2. Migrate 'eventual' back to 'unico'
        DB::table('activities')->where('frecuencia', 'eventual')->update(['frecuencia' => 'unico']);

        // 3. Add the old check constraint back
        DB::statement("ALTER TABLE activities ADD CONSTRAINT activities_frecuencia_check CHECK (frecuencia IN ('diario', 'semanal', 'mensual', 'trimestral', 'semestral', 'anual', 'unico'))");
        
        // Restore default value
        DB::statement("ALTER TABLE activities ALTER COLUMN frecuencia SET DEFAULT 'unico'");

        // 4. Drop the column
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('detalle_frecuencia');
        });
    }
};
