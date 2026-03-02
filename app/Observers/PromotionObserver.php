<?php

namespace App\Observers;

use App\Models\Promotion;
use App\Models\Activity;
use App\Enums\ActivityState;

class PromotionObserver
{
    /**
     * Handle the Promotion "created" event.
     */
    public function created(Promotion $promotion): void
    {
        if (!$promotion->activity_id) {
            $activity = new Activity([
                'program_id' => $promotion->program_id,
                'nombre' => $promotion->nombre_campana,
                'descripcion' => $promotion->descripcion ?? $promotion->nombre_campana,
                'tipo' => 'promocion',
                'frecuencia' => $promotion->frecuencia ?? 'unico',
                'estado' => ActivityState::PROGRAMADO,
                'unidad_medida' => 'Porcentaje',
                'fecha_inicio' => $promotion->fecha_programada,
                'fecha_fin' => $promotion->fecha_ejecucion ?? $promotion->fecha_programada,
                'responsable_id' => $promotion->responsable_id,
                'es_obligatoria' => false,
                'meta' => 100,
            ]);
            $activity->is_creating_from_ref = true;
            $activity->save();

            $promotion->activity_id = $activity->id;
            $promotion->proxima_ejecucion = $activity->proxima_ejecucion;
            $promotion->saveQuietly();
        }
    }

    /**
     * Handle the Promotion "updated" event.
     */
    public function updated(Promotion $promotion): void
    {
        if ($promotion->activity_id) {
            $activity = Activity::find($promotion->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $promotion->nombre_campana,
                    'descripcion' => $promotion->descripcion ?? $promotion->nombre_campana,
                    'frecuencia' => $promotion->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $promotion->fecha_programada,
                    'fecha_fin' => $promotion->fecha_ejecucion ?? $promotion->fecha_programada,
                    'responsable_id' => $promotion->responsable_id,
                ]);
            }
        }
    }
}
