<?php

namespace App\Observers;

use App\Models\Training;
use App\Models\Activity;
use App\Enums\ActivityState;

class TrainingObserver
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
            'reprogramado' => ActivityState::PROGRAMADO->value,
            default => ActivityState::PROGRAMADO->value,
        };
    }

    public function created(Training $training): void
    {
        if (!$training->activity_id) {
            $activity = Activity::create([
                'program_id' => $training->program_id,
                'nombre' => $training->tema,
                'descripcion' => $training->descripcion ?? $training->tema,
                'tipo' => 'capacitacion',
                'frecuencia' => $training->frecuencia ?? 'unico',
                'estado' => $this->mapStatus($training->estado),
                'unidad_medida' => 'Porcentaje',
                'fecha_inicio' => $training->fecha_programada,
                'fecha_fin' => $training->fecha_ejecucion ?? $training->fecha_programada,
                'responsable_id' => $training->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);

            $training->activity_id = $activity->id;
            $training->proxima_ejecucion = $activity->proxima_ejecucion;
            $training->saveQuietly();
        }
    }

    public function updated(Training $training): void
    {
        if ($training->activity_id) {
            $activity = Activity::find($training->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $training->tema,
                    'descripcion' => $training->descripcion ?? $training->tema,
                    'estado' => $this->mapStatus($training->estado),
                    'frecuencia' => $training->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $training->fecha_programada,
                    'fecha_fin' => $training->fecha_ejecucion ?? $training->fecha_programada,
                    'responsable_id' => $training->responsable_id,
                ]);
            }
        }
    }
}
