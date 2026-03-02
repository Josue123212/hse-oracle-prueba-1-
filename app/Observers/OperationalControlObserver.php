<?php

namespace App\Observers;

use App\Models\OperationalControl;
use App\Models\Activity;
use App\Enums\ActivityState;

class OperationalControlObserver
{
    /**
     * Handle the OperationalControl "created" event.
     */
    public function created(OperationalControl $control): void
    {
        if (!$control->activity_id) {
            $activity = new Activity([
                'program_id' => $control->program_id,
                'nombre' => $control->nombre_proceso,
                'descripcion' => $control->observaciones ?? $control->nombre_proceso,
                'tipo' => 'control_operacional',
                'frecuencia' => $control->frecuencia ?? 'unico',
                'estado' => ActivityState::PROGRAMADO,
                'unidad_medida' => 'Control',
                'fecha_inicio' => $control->fecha_programada,
                'fecha_fin' => $control->fecha_programada,
                'responsable_id' => $control->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);
            $activity->is_creating_from_ref = true;
            $activity->save();

            $control->activity_id = $activity->id;
            $control->proxima_ejecucion = $activity->proxima_ejecucion;
            $control->saveQuietly();
        }
    }

    /**
     * Handle the OperationalControl "updated" event.
     */
    public function updated(OperationalControl $control): void
    {
        if ($control->activity_id) {
            $activity = Activity::find($control->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $control->nombre_proceso,
                    'descripcion' => $control->observaciones ?? $control->nombre_proceso,
                    'frecuencia' => $control->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $control->fecha_programada,
                    'fecha_fin' => $control->fecha_programada,
                    'responsable_id' => $control->responsable_id,
                ]);
            }
        }
    }
}
