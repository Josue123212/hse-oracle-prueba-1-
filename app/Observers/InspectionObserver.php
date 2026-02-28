<?php

namespace App\Observers;

use App\Models\Inspection;
use App\Models\Activity;
use App\Enums\ActivityState;

class InspectionObserver
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
            'vencido', 'cancelado' => ActivityState::NO_CUMPLIO->value,
            'reprogramado' => ActivityState::PROGRAMADO->value,
            default => ActivityState::PROGRAMADO->value,
        };
    }

    public function created(Inspection $inspection): void
    {
        if (!$inspection->activity_id) {
            $activity = Activity::create([
                'program_id' => $inspection->program_id,
                'nombre' => $inspection->nombre,
                'descripcion' => $inspection->descripcion ?? $inspection->nombre,
                'tipo' => 'inspeccion',
                'frecuencia' => $inspection->frecuencia ?? 'unico',
                'estado' => $this->mapStatus($inspection->estado),
                'unidad_medida' => 'Porcentaje',
                'fecha_inicio' => $inspection->fecha_programada,
                'fecha_fin' => $inspection->fecha_programada,
                'responsable_id' => $inspection->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);

            $inspection->activity_id = $activity->id;
            $inspection->proxima_ejecucion = $activity->proxima_ejecucion;
            $inspection->saveQuietly();
        }
    }

    public function updated(Inspection $inspection): void
    {
        if ($inspection->activity_id) {
            $activity = Activity::find($inspection->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $inspection->nombre,
                    'descripcion' => $inspection->descripcion ?? $inspection->nombre,
                    'estado' => $this->mapStatus($inspection->estado),
                    'frecuencia' => $inspection->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $inspection->fecha_programada,
                    'fecha_fin' => $inspection->fecha_programada,
                    'responsable_id' => $inspection->responsable_id,
                ]);
            }
        }
    }
}
