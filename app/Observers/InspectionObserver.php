<?php

namespace App\Observers;

use App\Models\Inspection;
use App\Models\Activity;
use App\Enums\ActivityState;

class InspectionObserver
{
    /**
     * Handle the Inspection "created" event.
     */
    public function created(Inspection $inspection): void
    {
        if (!$inspection->activity_id) {
            $activity = new Activity([
                'program_id' => $inspection->program_id,
                'nombre' => $inspection->nombre,
                'descripcion' => $inspection->descripcion,
                'tipo' => 'inspeccion',
                'frecuencia' => $inspection->frecuencia ?? 'unico',
                'estado' => ActivityState::PROGRAMADO,
                'unidad_medida' => 'Porcentaje',
                'fecha_inicio' => $inspection->fecha_programada,
                'fecha_fin' => $inspection->fecha_programada,
                'responsable_id' => $inspection->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);
            $activity->is_creating_from_ref = true;
            $activity->save();

            $inspection->activity_id = $activity->id;
            $inspection->proxima_ejecucion = $activity->proxima_ejecucion;
            $inspection->saveQuietly();
        }
    }

    /**
     * Handle the Inspection "updated" event.
     */
    public function updated(Inspection $inspection): void
    {
        if ($inspection->activity_id) {
            $activity = Activity::find($inspection->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $inspection->nombre,
                    'descripcion' => $inspection->descripcion ?? $inspection->nombre,
                    'frecuencia' => $inspection->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $inspection->fecha_programada,
                    'fecha_fin' => $inspection->fecha_programada,
                    'responsable_id' => $inspection->responsable_id,
                ]);
            }
        }
    }
}
