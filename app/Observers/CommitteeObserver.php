<?php

namespace App\Observers;

use App\Models\Committee;
use App\Models\Activity;
use App\Enums\ActivityState;

class CommitteeObserver
{
    /**
     * Handle the Committee "created" event.
     */
    public function created(Committee $committee): void
    {
        if (!$committee->activity_id) {
            $activity = new Activity([
                'program_id' => $committee->program_id,
                'nombre' => $committee->nombre,
                'descripcion' => $committee->tema_principal ?? $committee->nombre,
                'tipo' => 'comite',
                'frecuencia' => $committee->frecuencia ?? 'unico',
                'estado' => ActivityState::PROGRAMADO,
                'unidad_medida' => 'Reunión',
                'fecha_inicio' => $committee->fecha_programada,
                'fecha_fin' => $committee->fecha_realizada ?? $committee->fecha_programada,
                'responsable_id' => $committee->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);
            $activity->is_creating_from_ref = true;
            $activity->save();

            $committee->activity_id = $activity->id;
            $committee->proxima_ejecucion = $activity->proxima_ejecucion;
            $committee->saveQuietly();
        }
    }

    /**
     * Handle the Committee "updated" event.
     */
    public function updated(Committee $committee): void
    {
        if ($committee->activity_id) {
            $activity = Activity::find($committee->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $committee->nombre,
                    'descripcion' => $committee->tema_principal ?? $committee->nombre,
                    'frecuencia' => $committee->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $committee->fecha_programada,
                    'fecha_fin' => $committee->fecha_realizada ?? $committee->fecha_programada,
                    'responsable_id' => $committee->responsable_id,
                ]);
            }
        }
    }
}
