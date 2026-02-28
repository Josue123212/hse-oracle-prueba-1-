<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\Activity;
use App\Enums\ActivityState;

class IncidentObserver
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
            'abierto', 'investigacion' => ActivityState::PROGRAMADO->value,
            'cerrado' => ActivityState::EJECUTADO->value,
            default => ActivityState::PROGRAMADO->value,
        };
    }

    public function created(Incident $incident): void
    {
        if (!$incident->activity_id) {
            $activity = Activity::create([
                'program_id' => $incident->program_id,
                'nombre' => $incident->titulo,
                'descripcion' => $incident->descripcion ?? $incident->titulo,
                'tipo' => 'incidente',
                'frecuencia' => $incident->frecuencia ?? 'unico',
                'estado' => $this->mapStatus($incident->estado),
                'unidad_medida' => 'Unidad',
                'fecha_inicio' => $incident->fecha_programada ?? $incident->fecha_ocurrencia,
                'fecha_fin' => $incident->fecha_programada ?? $incident->fecha_ocurrencia,
                'responsable_id' => $incident->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);

            $incident->activity_id = $activity->id;
            $incident->proxima_ejecucion = $activity->proxima_ejecucion;
            $incident->saveQuietly();
        }
    }

    public function updated(Incident $incident): void
    {
        if ($incident->activity_id) {
            $activity = Activity::find($incident->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $incident->titulo,
                    'descripcion' => $incident->descripcion ?? $incident->titulo,
                    'estado' => $this->mapStatus($incident->estado),
                    'frecuencia' => $incident->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $incident->fecha_programada ?? $incident->fecha_ocurrencia,
                    'fecha_fin' => $incident->fecha_programada ?? $incident->fecha_ocurrencia,
                    'responsable_id' => $incident->responsable_id,
                ]);
            }
        }
    }
}
