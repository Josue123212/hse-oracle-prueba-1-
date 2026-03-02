<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\Activity;
use App\Enums\ActivityState;

class IncidentObserver
{
    /**
     * Handle the Incident "created" event.
     */
    public function created(Incident $incident): void
    {
        if (!$incident->activity_id) {
            $activity = new Activity([
                'program_id' => $incident->program_id,
                'nombre' => $incident->titulo,
                'descripcion' => $incident->descripcion ?? $incident->titulo,
                'tipo' => 'incidente',
                'frecuencia' => $incident->frecuencia ?? 'eventual',
                'estado' => ActivityState::PROGRAMADO,
                'unidad_medida' => 'Evento',
                'fecha_inicio' => $incident->fecha_programada ?? $incident->fecha_ocurrencia,
                'fecha_fin' => $incident->fecha_programada ?? $incident->fecha_ocurrencia,
                'responsable_id' => $incident->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);
            $activity->is_creating_from_ref = true;
            $activity->save();

            $incident->activity_id = $activity->id;
            $incident->proxima_ejecucion = $activity->proxima_ejecucion;
            $incident->saveQuietly();
        }
    }

    /**
     * Handle the Incident "updated" event.
     */
    public function updated(Incident $incident): void
    {
        if ($incident->activity_id) {
            $activity = Activity::find($incident->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $incident->titulo,
                    'descripcion' => $incident->descripcion ?? $incident->titulo,
                    'frecuencia' => $incident->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $incident->fecha_programada ?? $incident->fecha_ocurrencia,
                    'fecha_fin' => $incident->fecha_programada ?? $incident->fecha_ocurrencia,
                    'responsable_id' => $incident->responsable_id,
                ]);
            }
        }
    }
}
