<?php

namespace App\Observers;

use App\Models\Committee;
use App\Models\Activity;
use App\Enums\ActivityState;

class CommitteeObserver
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
            'realizado' => ActivityState::EJECUTADO->value,
            'cancelado' => ActivityState::NO_CUMPLIO->value,
            default => ActivityState::PROGRAMADO->value,
        };
    }

    public function created(Committee $committee): void
    {
        if (!$committee->activity_id) {
            $activity = Activity::create([
                'program_id' => $committee->program_id,
                'nombre' => $committee->nombre,
                'descripcion' => $committee->tema_principal ?? $committee->nombre,
                'tipo' => 'comite',
                'frecuencia' => $committee->frecuencia ?? 'unico',
                'estado' => $this->mapStatus($committee->estado),
                'unidad_medida' => 'Porcentaje',
                'fecha_inicio' => $committee->fecha_programada,
                'fecha_fin' => $committee->fecha_realizada ?? $committee->fecha_programada,
                'responsable_id' => $committee->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);

            $committee->activity_id = $activity->id;
            $committee->proxima_ejecucion = $activity->proxima_ejecucion;
            $committee->saveQuietly();
        }
    }

    public function updated(Committee $committee): void
    {
        if ($committee->activity_id) {
            $activity = Activity::find($committee->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $committee->nombre,
                    'descripcion' => $committee->tema_principal ?? $committee->nombre,
                    'estado' => $this->mapStatus($committee->estado),
                    'frecuencia' => $committee->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $committee->fecha_programada,
                    'fecha_fin' => $committee->fecha_realizada ?? $committee->fecha_programada,
                    'responsable_id' => $committee->responsable_id,
                ]);
            }
        }
    }
}
