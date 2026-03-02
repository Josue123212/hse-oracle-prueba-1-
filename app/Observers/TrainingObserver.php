<?php

namespace App\Observers;

use App\Models\Training;
use App\Models\Activity;
use App\Enums\ActivityState;

class TrainingObserver
{
    /**
     * Handle the Training "created" event.
     */
    public function created(Training $training): void
    {
        if (!$training->activity_id) {
            $activity = new Activity([
                'program_id' => $training->program_id,
                'nombre' => $training->tema,
                'descripcion' => $training->descripcion,
                'tipo' => 'capacitacion',
                'frecuencia' => $training->frecuencia ?? 'unico',
                'estado' => ActivityState::PROGRAMADO,
                'unidad_medida' => 'Porcentaje',
                'fecha_inicio' => $training->fecha_programada,
                'fecha_fin' => $training->fecha_ejecucion ?? $training->fecha_programada,
                'responsable_id' => $training->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);
            $activity->is_creating_from_ref = true;
            $activity->save();

            $training->activity_id = $activity->id;
            $training->proxima_ejecucion = $activity->proxima_ejecucion;
            $training->saveQuietly();
        }
    }

    /**
     * Handle the Training "updated" event.
     */
    public function updated(Training $training): void
    {
        if ($training->activity_id) {
            $activity = Activity::find($training->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $training->tema,
                    'descripcion' => $training->descripcion,
                    'frecuencia' => $training->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $training->fecha_programada,
                    'fecha_fin' => $training->fecha_ejecucion ?? $training->fecha_programada,
                    'responsable_id' => $training->responsable_id,
                ]);
            }
        }
    }
}
