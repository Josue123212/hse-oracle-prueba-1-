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
        // 1. Drop existing constraints to allow updates
        $this->dropConstraints();

        // 2. Add 'resultado' column to tables that don't have it but need it
        $tablesRequiringResult = ['audits', 'operational_controls', 'trainings', 'drills', 'documentations'];
        foreach ($tablesRequiringResult as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'resultado')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('resultado')->nullable()->after('estado');
                });
            }
        }

        // 3. Migrate Data (Logic from Analysis)
        $this->migrateData('inspections');
        $this->migrateData('audits');
        $this->migrateData('trainings');
        $this->migrateData('promotions');
        $this->migrateData('drills');
        $this->migrateData('operational_controls');
        $this->migrateData('documentations');
        $this->migrateData('committees');

        // 4. Re-apply constraints with standard values
        $this->applyStandardConstraints();
    }

    protected function dropConstraints()
    {
        $tables = ['inspections', 'audits', 'trainings', 'promotions', 'drills', 'operational_controls', 'documentations', 'committees'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                // Try to drop common constraint names
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_estado_check");
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$table}_status_check");
            }
        }
    }

    protected function migrateData($table)
    {
        if (!Schema::hasTable($table)) return;

        // Map: Old State => New State
        // If Old State implies a result, we update 'resultado' too
        
        // Common mappings
        DB::table($table)->whereIn('estado', ['vencido', 'cancelado'])->update(['estado' => 'no_cumplio']);
        DB::table($table)->whereIn('estado', ['pendiente', 'planificado', 'reprogramado', 'borrador'])->update(['estado' => 'programado']);
        DB::table($table)->whereIn('estado', ['en_curso', 'revision'])->update(['estado' => 'en_proceso']);

        // Specific mappings for Result Extraction
        
        // Operational Controls: conforme/no_conforme -> ejecutado + result
        if ($table === 'operational_controls') {
            DB::table($table)->where('estado', 'conforme')->update(['resultado' => 'conforme', 'estado' => 'ejecutado']);
            DB::table($table)->where('estado', 'no_conforme')->update(['resultado' => 'no_conforme', 'estado' => 'ejecutado']);
        }

        // Documentations: aprobado/obsoleto -> ejecutado + result
        if ($table === 'documentations') {
            DB::table($table)->where('estado', 'aprobado')->update(['resultado' => 'vigente', 'estado' => 'ejecutado']);
            DB::table($table)->where('estado', 'obsoleto')->update(['resultado' => 'obsoleto', 'estado' => 'ejecutado']);
        }

        // Inspections/Audits: ejecutado -> ejecutado (result is already handled or will be null initially)
        // Ensure everything else falls into valid buckets
        // For safety, any unknown state defaults to 'programado' if not 'ejecutado', 'en_proceso', 'no_cumplio'
        DB::table($table)->whereNotIn('estado', ['programado', 'en_proceso', 'ejecutado', 'no_cumplio'])->update(['estado' => 'programado']);
    }

    protected function applyStandardConstraints()
    {
        $tables = ['inspections', 'audits', 'trainings', 'promotions', 'drills', 'operational_controls', 'documentations', 'committees'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_estado_check CHECK (estado IN ('programado', 'en_proceso', 'ejecutado', 'no_cumplio'))");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this is complex because data was lossy (many states mapped to one).
        // We will just remove the strict constraint to allow previous values if needed manually.
        $this->dropConstraints();
    }
};
