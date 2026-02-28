<?php

namespace App\Observers;

use App\Models\Promotion;
use App\Models\Activity;
use App\Enums\ActivityState;

class PromotionObserver
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
            'planificado', 'reprogramado' => ActivityState::PROGRAMADO->value,
            'en_curso' => ActivityState::EN_PROCESO->value,
            'finalizado', 'realizado' => ActivityState::EJECUTADO->value,
            'cancelado', 'vencido' => ActivityState::NO_CUMPLIO->value,
            default => ActivityState::PROGRAMADO->value,
        };
    }

    public function created(Promotion $promotion): void
    {
        if (!$promotion->activity_id) {
            $activity = Activity::create([
                'program_id' => $promotion->program_id,
                'nombre' => $promotion->nombre_campana,
                'descripcion' => $promotion->descripcion ?? $promotion->nombre_campana,
                'tipo' => 'promocion',
                'frecuencia' => $promotion->frecuencia ?? 'unico',
                'estado' => $this->mapStatus($promotion->estado),
                'unidad_medida' => 'Evento',
                'fecha_inicio' => $promotion->fecha_inicio,
                'fecha_fin' => $promotion->fecha_fin ?? $promotion->fecha_inicio,
                'responsable_id' => $promotion->responsable_id,
                'es_obligatoria' => true,
                'meta' => 100,
            ]);

            $promotion->activity_id = $activity->id;
            $promotion->proxima_ejecucion = $activity->proxima_ejecucion;
            $promotion->saveQuietly();
        }
    }

    public function updated(Promotion $promotion): void
    {
        if ($promotion->activity_id) {
            $activity = Activity::find($promotion->activity_id);
            if ($activity) {
                $activity->update([
                    'nombre' => $promotion->nombre_campana,
                    'descripcion' => $promotion->descripcion ?? $promotion->nombre_campana,
                    'estado' => $this->mapStatus($promotion->estado),
                    'frecuencia' => $promotion->frecuencia ?? $activity->frecuencia,
                    'fecha_inicio' => $promotion->fecha_programada,
                    'fecha_fin' => $promotion->fecha_programada,
                    'responsable_id' => $promotion->responsable_id,
                ]);
            }
        }
    }
}
