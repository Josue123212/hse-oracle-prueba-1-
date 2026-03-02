<?php

namespace App\Observers;

use App\Models\Drill;
use App\Models\Activity;
use App\Enums\ActivityState;

class DrillObserver
{
    /**
     * Handle the Drill "created" event.
     */
    public function created(Drill $drill): void
    {
        if (!$drill->activity_id) {
            $activity = new Activity([
                'program_id' => $drill->program_id,
                'nombre' => $drill->nombre,
                'descripcion' => $drill->descripcion ?? $drill->nombre,
                'tipo' => 'simulacro',
                'frecuencia' => $drill->frecuencia ?? 'unico',
                'estado' => ActivityState::PROGRAMADO,
                'unidad_medida' => 'Simulacros',
                'fecha_inicio' => $drill->fecha_programada,
                'fecha_fin' => $drill->fecha_ejecucion ?? $drill->fecha_programada,
                'responsable_id' => $drill->responsable_id, // Asumiendo que hay un responsable
                'es_obligatoria' => true,
                'meta' => 100,
            ]);
            $activity->is_creating_from_ref = true;
            $activity->save();

            $drill->activity_id = $activity->id;
            $drill->proxima_ejecucion = $activity->proxima_ejecucion;
            $drill->saveQuietly();
        }
    }

    /**
     * Handle the Drill "updated" event.
     */
    public function updated(Drill $drill): void
    {
        if ($drill->activity_id) {
            $activity = Activity::find($drill->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $drill->nombre,
                    'descripcion' => $drill->descripcion ?? $drill->nombre,
                    'frecuencia' => $drill->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $drill->fecha_programada,
                    'fecha_fin' => $drill->fecha_ejecucion ?? $drill->fecha_programada,
                ]);
            }
        }
    }
}
