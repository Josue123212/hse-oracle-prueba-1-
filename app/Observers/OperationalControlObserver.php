<?php

namespace App\Observers;

use App\Models\OperationalControl;
use App\Models\Activity;
use App\Enums\ActivityState;

class OperationalControlObserver
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
            'pendiente' => ActivityState::PROGRAMADO->value,
            'conforme', 'no_conforme' => ActivityState::EJECUTADO->value, // Executed regardless of result
            default => ActivityState::PROGRAMADO->value,
        };
    }

    public function created(OperationalControl $control): void
    {
        if (!$control->activity_id) {
            $activity = Activity::create([
                'program_id' => $control->program_id,
                'nombre' => $control->nombre_proceso,
                'descripcion' => $control->observaciones ?? $control->nombre_proceso,
                'tipo' => 'control_operacional',
                'frecuencia' => $control->frecuencia ?? 'unico',
                'estado' => $this->mapStatus($control->estado),
                'unidad_medida' => 'Control',
                'fecha_inicio' => $control->fecha_programada,
                'fecha_fin' => $control->fecha_programada,
                'responsable_id' => $control->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);

            $control->activity_id = $activity->id;
            $control->proxima_ejecucion = $activity->proxima_ejecucion;
            $control->saveQuietly();
        }
    }

    public function updated(OperationalControl $control): void
    {
        if ($control->activity_id) {
            $activity = Activity::find($control->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $control->nombre_proceso,
                    'descripcion' => $control->observaciones ?? $control->nombre_proceso,
                    'estado' => $this->mapStatus($control->estado),
                    'frecuencia' => $control->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $control->fecha_programada,
                    'fecha_fin' => $control->fecha_programada,
                    'responsable_id' => $control->responsable_id,
                ]);
            }
        }
    }
}
