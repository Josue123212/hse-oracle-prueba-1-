<?php

namespace App\Observers;

use App\Models\Activity;
use App\Enums\ActivityState;
use Carbon\Carbon;

class ActivityObserver
{
    public function saving(Activity $activity): void
    {
        // Ensure state is valid enum if passed as string
        if (is_string($activity->estado)) {
             $activity->estado = ActivityState::tryFrom($activity->estado) ?? ActivityState::PROGRAMADO;
        }

        // 1. Eventual Logic: Always Ejecutado
        if ($activity->frecuencia === 'eventual') {
            $activity->estado = ActivityState::EJECUTADO;
            return;
        }

        $veces = (int) ($activity->veces_al_anio ?? 1);
        $ejecuciones = (int) ($activity->ejecuciones_realizadas ?? 0);

        // 2. Handle Manual State Changes (Auto-increment logic)
        // If user manually changes state but doesn't touch executions, we infer intent
        if ($activity->isDirty('estado') && !$activity->isDirty('ejecuciones_realizadas')) {
            // If changing to EJECUTADO
            if ($activity->estado === ActivityState::EJECUTADO) {
                if ($ejecuciones < $veces) {
                    $ejecuciones++;
                    $activity->ejecuciones_realizadas = $ejecuciones;
                }
            }
            // If changing to EN_PROCESO
            elseif ($activity->estado === ActivityState::EN_PROCESO) {
                if ($veces <= 1) {
                    // 1-rep activity starting -> Mark as Executed immediately
                    $activity->estado = ActivityState::EJECUTADO;
                    if ($ejecuciones < 1) {
                        $ejecuciones++;
                        $activity->ejecuciones_realizadas = $ejecuciones;
                    }
                } elseif ($ejecuciones == 0) {
                    // Multi-rep starting -> Increment to 1 to signify start
                    $ejecuciones++;
                    $activity->ejecuciones_realizadas = $ejecuciones;
                }
            }
        }

        // 3. Enforce Consistency based on Executions (Absolute Rules)
        if ($ejecuciones >= $veces) {
            $activity->estado = ActivityState::EJECUTADO;
        } elseif ($ejecuciones > 0) {
            // If executions started but not finished
            // Allow NO_CUMPLIO if manually set or timed out
            if ($activity->estado !== ActivityState::NO_CUMPLIO) {
                $activity->estado = ActivityState::EN_PROCESO;
            }
        } else {
            // executions == 0
            // If state is Executed or In Progress but 0 executions -> Invalid -> Programado
            if ($activity->estado === ActivityState::EJECUTADO || $activity->estado === ActivityState::EN_PROCESO) {
                 $activity->estado = ActivityState::PROGRAMADO;
            }
        }
        
        // 4. Date Logic (Sync with time rules)
        // Only if not Executed and not manually set to No Cumplio (unless date changed)
        if ($activity->estado !== ActivityState::EJECUTADO) {
            $nextDate = $activity->next_execution_date;
            // If date is strictly past -> No Cumplio
            if ($nextDate && $nextDate->lt(Carbon::today())) {
                $activity->estado = ActivityState::NO_CUMPLIO;
            }
            // If no date and not eventual -> No Cumplio (User rule: Proxima ejecucion (-) y es anual -> no cumplio)
            elseif (!$nextDate && $activity->frecuencia !== 'eventual') {
                 $activity->estado = ActivityState::NO_CUMPLIO;
            }
        }

        // 5. Calculate and store next execution date
        $activity->proxima_ejecucion = $activity->frecuencia === 'eventual' 
            ? null 
            : $activity->next_execution_date;
    }

    public function updated(Activity $activity): void
    {
        // Prevent infinite loops if the child update triggers an activity update
        if ($activity->isDirty(['fecha_inicio', 'fecha_fin', 'estado', 'responsable_id', 'proxima_ejecucion'])) {
            
            switch ($activity->tipo) {
                case 'documentacion':
                    if ($activity->documentation) {
                        $activity->documentation->updateQuietly([
                            'fecha_programada' => $activity->fecha_inicio,
                            'fecha_aprobacion' => $activity->fecha_fin, // Assuming end date maps to approval
                            'proxima_ejecucion' => $activity->proxima_ejecucion,
                            'estado' => $this->mapStateToChild($activity->estado, 'documentacion'),
                            'responsable_id' => $activity->responsable_id,
                        ]);
                    }
                    break;

                case 'auditoria':
                    if ($activity->audit) {
                        $activity->audit->updateQuietly([
                            'fecha_programada' => $activity->fecha_inicio,
                            'fecha_ejecucion' => $activity->fecha_fin,
                            'proxima_ejecucion' => $activity->proxima_ejecucion,
                            'estado' => $this->mapStateToChild($activity->estado, 'auditoria'),
                            'auditor_id' => $activity->responsable_id, // Map responsible to auditor
                        ]);
                    }
                    break;

                case 'inspeccion':
                    if ($activity->inspection) {
                        $activity->inspection->updateQuietly([
                            'fecha_programada' => $activity->fecha_inicio,
                            'proxima_ejecucion' => $activity->proxima_ejecucion,
                            'estado' => $this->mapStateToChild($activity->estado, 'inspeccion'),
                            'responsable_id' => $activity->responsable_id,
                        ]);
                    }
                    break;

                case 'capacitacion':
                    if ($activity->training) {
                        $activity->training->updateQuietly([
                            'fecha_programada' => $activity->fecha_inicio,
                            'fecha_ejecucion' => $activity->fecha_fin,
                            'proxima_ejecucion' => $activity->proxima_ejecucion,
                            'estado' => $this->mapStateToChild($activity->estado, 'capacitacion'),
                            'responsable_id' => $activity->responsable_id,
                        ]);
                    }
                    break;

                case 'simulacro':
                    if ($activity->drill) {
                        $activity->drill->updateQuietly([
                            'fecha_programada' => $activity->fecha_inicio,
                            'fecha_ejecucion' => $activity->fecha_fin,
                            'proxima_ejecucion' => $activity->proxima_ejecucion,
                            'estado' => $this->mapStateToChild($activity->estado, 'simulacro'),
                            'responsable_id' => $activity->responsable_id,
                        ]);
                    }
                    break;

                case 'control_operacional':
                    if ($activity->operationalControl) {
                        $activity->operationalControl->updateQuietly([
                            'fecha_programada' => $activity->fecha_inicio,
                            'proxima_ejecucion' => $activity->proxima_ejecucion,
                            'estado' => $this->mapStateToChild($activity->estado, 'control_operacional'),
                            'responsable_id' => $activity->responsable_id,
                        ]);
                    }
                    break;

                case 'incidente':
                    if ($activity->incident) {
                        $activity->incident->updateQuietly([
                            'fecha_programada' => $activity->fecha_inicio, // Map start to scheduled/occurrence
                            'proxima_ejecucion' => $activity->proxima_ejecucion,
                            'estado' => $this->mapStateToChild($activity->estado, 'incidente'),
                            'responsable_id' => $activity->responsable_id,
                        ]);
                    }
                    break;

                case 'comite':
                    if ($activity->committee) {
                        $activity->committee->updateQuietly([
                            'fecha_programada' => $activity->fecha_inicio,
                            'fecha_realizada' => $activity->fecha_fin,
                            'proxima_ejecucion' => $activity->proxima_ejecucion,
                            'estado' => $this->mapStateToChild($activity->estado, 'comite'),
                            'responsable_id' => $activity->responsable_id,
                        ]);
                    }
                    break;

                case 'promocion':
                    if ($activity->promotion) {
                        $activity->promotion->updateQuietly([
                            'fecha_programada' => $activity->fecha_inicio,
                            'proxima_ejecucion' => $activity->proxima_ejecucion,
                            'estado' => $this->mapStateToChild($activity->estado, 'promocion'),
                            'responsable_id' => $activity->responsable_id,
                        ]);
                    }
                    break;
            }
        }
    }

    public function deleted(Activity $activity): void
    {
        \Livewire\Livewire::dispatch('activity-updated');
    }

    private function mapStateToChild(string|ActivityState $activityState, string $type): string
    {
        $stateValue = $activityState instanceof ActivityState ? $activityState->value : $activityState;

        if ($type === 'documentacion') {
            return match ($stateValue) {
                'programado' => 'borrador',
                'en_proceso' => 'revision',
                'ejecutado' => 'aprobado',
                'no_cumplio' => 'obsoleto',
                default => 'borrador',
            };
        }

        // Default mapping for other types using standard activity states
        return match ($stateValue) {
            'programado' => 'programado',
            'en_proceso' => 'en_proceso',
            'ejecutado' => 'ejecutado',
            'no_cumplio' => 'no_cumplio',
            default => 'programado',
        };
    }
}
