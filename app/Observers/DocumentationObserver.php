<?php

namespace App\Observers;

use App\Models\Documentation;
use App\Models\Activity;
use App\Enums\ActivityState;

class DocumentationObserver
{
    /**
     * Handle the Documentation "created" event.
     */
    public function created(Documentation $documentation): void
    {
        if (!$documentation->activity_id) {
            $activity = new Activity([
                'program_id' => $documentation->program_id,
                'nombre' => $documentation->titulo,
                'descripcion' => $documentation->descripcion ?? $documentation->titulo,
                'tipo' => 'documentacion',
                'frecuencia' => $documentation->frecuencia ?? 'unico',
                'estado' => ActivityState::PROGRAMADO,
                'unidad_medida' => 'Documento',
                'fecha_inicio' => $documentation->fecha_programada,
                'fecha_fin' => $documentation->fecha_aprobacion ?? $documentation->fecha_programada,
                'responsable_id' => $documentation->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);
            $activity->is_creating_from_ref = true;
            $activity->save();

            $documentation->activity_id = $activity->id;
            $documentation->proxima_ejecucion = $activity->proxima_ejecucion;
            $documentation->saveQuietly();
        }
    }

    /**
     * Handle the Documentation "updated" event.
     */
    public function updated(Documentation $documentation): void
    {
        if ($documentation->activity_id) {
            $activity = Activity::find($documentation->activity_id);
            if ($activity) {
                $updates = [
                    'nombre' => $documentation->titulo,
                    'descripcion' => $documentation->descripcion ?? $documentation->titulo,
                    'fecha_inicio' => $documentation->fecha_programada ?? $activity->fecha_inicio,
                    'fecha_fin' => $documentation->fecha_aprobacion ?? $documentation->fecha_programada ?? $activity->fecha_fin,
                    'responsable_id' => $documentation->responsable_id,
                ];

                if ($documentation->isDirty(['frecuencia', 'veces_al_anio', 'ejecuciones_realizadas', 'detalle_frecuencia'])) {
                    $updates['frecuencia'] = $documentation->frecuencia;
                    $updates['veces_al_anio'] = $documentation->veces_al_anio;
                    $updates['ejecuciones_realizadas'] = $documentation->ejecuciones_realizadas;
                    $updates['detalle_frecuencia'] = $documentation->detalle_frecuencia;
                }

                $activity->update($updates);
            }
        }
    }
}
