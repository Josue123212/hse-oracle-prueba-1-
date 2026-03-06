<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityExecution;
use App\Enums\ActivityState;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateActivitiesToExecutedSeeder extends Seeder
{
    public function run(): void
    {
        // Fecha límite: 8 de marzo de 2026
        $cutoffDate = Carbon::create(2026, 3, 8)->startOfDay();

        $this->command->info("Actualizando actividades programadas antes del {$cutoffDate->format('Y-m-d')} a estado EJECUTADO...");

        $count = ActivityExecution::where('fecha_programada', '<', $cutoffDate)
            ->where('estado', '!=', ActivityState::EJECUTADO)
            ->update([
                'estado' => ActivityState::EJECUTADO,
                // Asignamos la fecha programada como fecha de ejecución real por defecto
                'fecha_ejecucion_real' => DB::raw('fecha_programada'),
                'updated_at' => now(),
            ]);

        $this->command->info("Se actualizaron {$count} actividades al estado EJECUTADO.");
    }
}
