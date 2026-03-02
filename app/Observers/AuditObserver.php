<?php

namespace App\Observers;

use App\Models\Audit;
use App\Models\Activity;
use App\Enums\ActivityState;

class AuditObserver
{
    /**
     * Handle the Audit "created" event.
     */
    public function created(Audit $audit): void
    {
        // Si la auditoría no tiene una actividad asociada, crear una
        if (!$audit->activity_id) {
            $activity = new Activity([
                'program_id' => $audit->program_id,
                'nombre' => $audit->nombre,
                'descripcion' => $audit->descripcion,
                'tipo' => 'auditoria', // Corregido a minúsculas
                'frecuencia' => $audit->frecuencia ?? 'unico', // Cambiado a 'unico' por defecto
                'estado' => ActivityState::PROGRAMADO,
                'unidad_medida' => 'Porcentaje', // Valor por defecto
                'fecha_inicio' => $audit->fecha_programada,
                'fecha_fin' => $audit->fecha_vencimiento ?? $audit->fecha_programada,
                'responsable_id' => $audit->auditor_id,
                'es_obligatoria' => true, // Asumimos que las auditorías son obligatorias
                'meta' => 100, // Valor por defecto
            ]);
            $activity->is_creating_from_ref = true;
            $activity->save();

            // Actualizar la auditoría con el ID de la actividad
            // Usamos quiet() para evitar disparar el evento updated y causar un bucle
            $audit->activity_id = $activity->id;
            $audit->proxima_ejecucion = $activity->proxima_ejecucion;
            $audit->saveQuietly();
        }
    }

    /**
     * Handle the Audit "updated" event.
     */
    public function updated(Audit $audit): void
    {
        // Sincronizar cambios con la actividad asociada
        if ($audit->activity_id) {
            $activity = Activity::find($audit->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $audit->nombre,
                    'descripcion' => $audit->descripcion,
                    'frecuencia' => $audit->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $audit->fecha_programada,
                    'fecha_fin' => $activity->fecha_fin,
                    'responsable_id' => $audit->auditor_id,
                ]);
            }
        }
    }

    /**
     * Handle the Audit "deleted" event.
     */
    public function deleted(Audit $audit): void
    {
        // Opcional: Eliminar la actividad si se elimina la auditoría
        // if ($audit->activity_id) {
        //     Activity::destroy($audit->activity_id);
        // }
    }


    /**
     * Handle the Audit "restored" event.
     */
    public function restored(Audit $audit): void
    {
        //
    }

    /**
     * Handle the Audit "force deleted" event.
     */
    public function forceDeleted(Audit $audit): void
    {
        //
    }
}
