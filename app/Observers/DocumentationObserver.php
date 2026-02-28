<?php

namespace App\Observers;

use App\Models\Documentation;
use App\Models\Activity;
use App\Enums\ActivityState;

class DocumentationObserver
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
            'borrador', 'revision' => ActivityState::PROGRAMADO->value,
            'aprobado' => ActivityState::EJECUTADO->value,
            'obsoleto' => ActivityState::NO_CUMPLIO->value,
            default => ActivityState::PROGRAMADO->value,
        };
    }

    public function created(Documentation $documentation): void
    {
        if (!$documentation->activity_id) {
            $activity = Activity::create([
                'program_id' => $documentation->program_id,
                'nombre' => $documentation->titulo,
                'descripcion' => $documentation->descripcion ?? $documentation->titulo,
                'tipo' => 'documentacion',
                'frecuencia' => $documentation->frecuencia ?? 'unico',
                'estado' => $this->mapStatus($documentation->estado),
                'unidad_medida' => 'Documento',
                'fecha_inicio' => $documentation->fecha_programada ?? now(),
                'fecha_fin' => $documentation->fecha_aprobacion ?? $documentation->fecha_programada ?? now(),
                'responsable_id' => $documentation->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);

            $documentation->activity_id = $activity->id;
            $documentation->proxima_ejecucion = $activity->proxima_ejecucion;
            $documentation->saveQuietly();
        }
    }

    public function updated(Documentation $documentation): void
    {
        if ($documentation->activity_id) {
            $activity = Activity::find($documentation->activity_id);
            if ($activity) {
                $updates = [
                    'nombre' => $documentation->titulo,
                    'descripcion' => $documentation->descripcion ?? $documentation->titulo,
                    'estado' => $this->mapStatus($documentation->estado),
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
