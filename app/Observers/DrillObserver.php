<?php

namespace App\Observers;

use App\Models\Drill;
use App\Models\Activity;
use App\Enums\ActivityState;

class DrillObserver
{
    private function mapStatus(string|ActivityState $status): string
    {
        $statusValue = $status instanceof ActivityState ? $status->value : $status;

        return match ($statusValue) {
            ActivityState::PROGRAMADO->value => ActivityState::PROGRAMADO->value,
            ActivityState::EN_PROCESO->value => ActivityState::EN_PROCESO->value,
            ActivityState::EJECUTADO->value => ActivityState::EJECUTADO->value,
            ActivityState::NO_CUMPLIO->value => ActivityState::NO_CUMPLIO->value,
            // Legacy mappings
            'cancelado', 'vencido' => ActivityState::NO_CUMPLIO->value,
            default => ActivityState::PROGRAMADO->value,
        };
    }

    public function created(Drill $drill): void
    {
        if (!$drill->activity_id) {
            $activity = Activity::create([
                'program_id' => $drill->program_id,
                'nombre' => $drill->nombre,
                'descripcion' => $drill->descripcion ?? $drill->nombre,
                'tipo' => 'simulacro',
                'frecuencia' => $drill->frecuencia ?? 'unico',
                'estado' => $this->mapStatus($drill->estado),
                'unidad_medida' => 'Porcentaje',
                'fecha_inicio' => $drill->fecha_programada,
                'fecha_fin' => $drill->fecha_ejecucion ?? $drill->fecha_programada,
                // Drill usually doesn't have responsable_id in migration? Need to check.
                // Assuming it might use auth user or default if missing.
                // Let's check Drill model/migration later. If missing, default to 1 or null.
                'responsable_id' => auth()->id() ?? 1, 
                'es_obligatoria' => true,
                'meta' => 100,
            ]);

            $drill->activity_id = $activity->id;
            $drill->proxima_ejecucion = $activity->proxima_ejecucion;
            $drill->saveQuietly();
        }
    }

    public function updated(Drill $drill): void
    {
        if ($drill->activity_id) {
            $activity = Activity::find($drill->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $drill->nombre,
                    'descripcion' => $drill->descripcion ?? $drill->nombre,
                    'estado' => $this->mapStatus($drill->estado),
                    'frecuencia' => $drill->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $drill->fecha_programada,
                    'fecha_fin' => $drill->fecha_ejecucion ?? $drill->fecha_programada,
                ]);
            }
        }
    }
}
