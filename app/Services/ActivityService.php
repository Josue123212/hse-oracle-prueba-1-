<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityExecution;
use App\Models\Inspection;
use App\Models\Audit;
use App\Models\Training;
use App\Models\Drill;
use App\Models\Incident;
use App\Models\Committee;
use App\Models\Documentation;
use App\Models\Promotion;
use App\Models\OperationalControl;
use App\Enums\ActivityState;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class ActivityService
{
    /**
     * Crea una nueva actividad y su registro satélite correspondiente (Inspección, Auditoría, etc.)
     * asegurando la integridad referencial y la lógica de negocio.
     */
    public function createWithType(array $data, string $type): Model
    {
        return DB::transaction(function () use ($data, $type) {
            // 1. Preparar datos comunes para la Actividad
            $defaults = [
                'frecuencia' => 'unico',
                'unidad_medida' => 'Porcentaje',
                'es_obligatoria' => true,
                'meta' => 100,
                'veces_al_anio' => 1,
                'ejecuciones_realizadas' => 0,
            ];
            
            // Merge defaults with data, data takes precedence
            $finalData = array_merge($defaults, $data);
            
            // Map program_id_selector to program_id for satellite models if needed
            if (isset($finalData['program_id_selector'])) {
                $finalData['program_id'] = $finalData['program_id_selector'];
            }

            // Determine dates
            $fechaInicio = $finalData['fecha_inicio'] ?? $finalData['fecha_programada'] ?? now();
            $fechaFin = $finalData['fecha_fin'] ?? $fechaInicio;

            $activityData = [
                'program_component_id' => $finalData['program_component_id'] ?? null,
                'nombre' => $finalData['nombre'],
                'descripcion' => $finalData['descripcion'] ?? $finalData['nombre'],
                'tipo' => $type,
                'frecuencia' => $finalData['frecuencia'],
                'unidad_medida' => $finalData['unidad_medida'],
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'responsable_id' => $finalData['responsable_id'] ?? null,
                'es_obligatoria' => $finalData['es_obligatoria'],
                'meta' => $finalData['meta'],
                'veces_al_anio' => $finalData['veces_al_anio'],
                'ejecuciones_realizadas' => $finalData['ejecuciones_realizadas'],
                // Otros campos específicos si son necesarios
            ];

            // Handle logic for eventual frequency
            if ($activityData['frecuencia'] === 'eventual') {
                 // ...
            }

            // 2. Crear la Actividad Padre
            $activity = Activity::create($activityData);

            // 3. Crear el registro Satélite vinculado
            $satelliteData = array_merge($finalData, ['activity_id' => $activity->id]);
            
            // Obtener el program_id desde el componente para los modelos satélite
            if ($activity->program_component_id) {
                $component = \App\Models\ProgramComponent::find($activity->program_component_id);
                if ($component) {
                    $satelliteData['program_id'] = $component->program_id;
                }
            }

            // Ensure fecha_programada is set for satellite
            if (!isset($satelliteData['fecha_programada'])) {
                $satelliteData['fecha_programada'] = $fechaInicio;
            }

            // Define common satellite fields (added by recent migrations to all modules)
            $commonSatelliteFields = [
                'program_id', 'activity_id', 'frecuencia', 'veces_al_anio', 
                'ejecuciones_realizadas', 'detalle_frecuencia'
            ];

            // Define specific fields per type
            $specificFields = match ($type) {
                'inspeccion' => [
                    'nombre', 'descripcion', 'fecha_programada', 'fecha_ejecucion', 
                    'responsable_id', 'location_id', 'observaciones', 'lugar', 
                    'tipo_inspeccion', 'resultado'
                ],
                'auditoria' => [
                    'nombre', 'descripcion', 'fecha_programada', 'fecha_ejecucion', 
                    'hallazgos', 'auditor_id', 'responsable_id', 'tipo_auditoria', 
                    'entidad_auditora', 'alcance', 'auditores'
                ],
                'capacitacion' => [
                    'tema', 'descripcion', 'fecha_programada', 'hora_inicio', 
                    'duracion_horas', 'asistentes_esperados', 'asistentes_reales', 
                    'responsable_id', 'fecha_ejecucion'
                ],
                'simulacro' => [
                    'nombre', 'descripcion', 'fecha_programada', 'fecha_ejecucion', 
                    'escenario', 'evaluacion', 'participantes_count', 'responsable_id'
                ],
                'incidente' => [
                    'titulo', 'descripcion', 'fecha_ocurrencia', 'lugar', 'severidad', 
                    'acciones_inmediatas', 'causa_raiz', 'responsable_id', 'fecha_programada'
                ],
                'comite' => [
                    'nombre', 'tema_principal', 'fecha_programada', 'fecha_realizada', 
                    'acuerdos', 'acta_path', 'responsable_id'
                ],
                'documentacion' => [
                    'titulo', 'tipo_documento', 'descripcion', 'version', 'archivo_path', 
                    'fecha_aprobacion', 'responsable_id', 'fecha_programada'
                ],
                'promocion' => [
                    'nombre_campana', 'descripcion', 'fecha_programada', 'publico_objetivo', 
                    'material_entregado', 'participantes_estimados', 'participantes_reales', 
                    'responsable_id'
                ],
                'control_operacional' => [
                    'nombre_proceso', 'parametro', 'valor_esperado', 'valor_medido', 
                    'fecha_programada', 'observaciones', 'responsable_id'
                ],
                'general' => [],
                default => []
            };

            // Apply mappings for fields that have different names in satellite vs activity
            if ($type === 'capacitacion') {
                $satelliteData['tema'] = $satelliteData['tema'] ?? $data['nombre'];
            } elseif ($type === 'incidente') {
                $satelliteData['titulo'] = $satelliteData['titulo'] ?? $data['nombre'];
                $satelliteData['fecha_ocurrencia'] = $satelliteData['fecha_ocurrencia'] ?? $fechaInicio;
            } elseif ($type === 'documentacion') {
                $satelliteData['titulo'] = $satelliteData['titulo'] ?? $data['nombre'];
                $satelliteData['tipo_documento'] = $satelliteData['tipo_documento'] ?? 'General';
                $satelliteData['version'] = $satelliteData['version'] ?? '1.0';
            } elseif ($type === 'promocion') {
                $satelliteData['nombre_campana'] = $satelliteData['nombre_campana'] ?? $data['nombre'];
            } elseif ($type === 'control_operacional') {
                $satelliteData['nombre_proceso'] = $satelliteData['nombre_proceso'] ?? $data['nombre'];
                $satelliteData['parametro'] = $satelliteData['parametro'] ?? 'General';
                $satelliteData['valor_esperado'] = $satelliteData['valor_esperado'] ?? 'N/A';
            }

            // Filter data
            $allowedFields = array_merge($commonSatelliteFields, $specificFields);
            $satelliteDataFiltered = Arr::only($satelliteData, $allowedFields);

            // Handle 'general' type which has no satellite
            if ($type === 'general') {
                return $activity;
            }

            $satellite = match ($type) {
                'inspeccion' => Inspection::create($satelliteDataFiltered),
                'auditoria' => Audit::create($satelliteDataFiltered),
                'capacitacion' => Training::create($satelliteDataFiltered),
                'simulacro' => Drill::create($satelliteDataFiltered),
                'incidente' => Incident::create($satelliteDataFiltered),
                'comite' => Committee::create($satelliteDataFiltered),
                'documentacion' => Documentation::create($satelliteDataFiltered),
                'promocion' => Promotion::create($satelliteDataFiltered),
                'control_operacional' => OperationalControl::create($satelliteDataFiltered),
                default => throw new \InvalidArgumentException("Tipo de actividad no soportado: $type"),
            };

            // 4. Calcular y actualizar próxima ejecución inicial
            $this->generateExecutions($activity);
            $this->calculateNextExecution($activity);

            return $satellite;
        });
    }

    /**
     * Actualiza una actividad y gestiona su registro satélite.
     * Centraliza la lógica de cambio de tipo y actualización de hijos.
     */
    public function updateActivity(Activity $activity, array $data): Activity
    {
        return DB::transaction(function () use ($activity, $data) {
            // 1. Detectar si hubo cambio de tipo
            $oldType = $activity->tipo;
            $newType = $data['tipo'] ?? $oldType;
            $typeChanged = $oldType !== $newType;

            // 2. Actualizar la Actividad Padre
            // Filtrar campos que son de la actividad explícitamente para evitar problemas
            // con campos extra que vengan en $data
            $activityFillable = [
                'program_id', 'nombre', 'descripcion', 'tipo', 'frecuencia', 
                'estado', 'unidad_medida', 'fecha_inicio', 'fecha_fin', 
                'responsable_id', 'es_obligatoria', 'meta', 'veces_al_anio',
                'responsable_delegado_id', 'apoyo', 'location_id', 
                'detalle_frecuencia'
            ];
            $activityData = Arr::only($data, $activityFillable);
            
            $activity->update($activityData);

            // 3. Gestionar Satélites
            if ($typeChanged) {
                // Eliminar satélite anterior
                match ($oldType) {
                    'inspeccion' => $activity->inspection()?->delete(),
                    'auditoria' => $activity->audit()?->delete(),
                    'capacitacion' => $activity->training()?->delete(),
                    'simulacro' => $activity->drill()?->delete(),
                    'incidente' => $activity->incident()?->delete(),
                    'comite' => $activity->committee()?->delete(),
                    'documentacion' => $activity->documentation()?->delete(),
                    'promocion' => $activity->promotion()?->delete(),
                    'control_operacional' => $activity->operationalControl()?->delete(),
                    default => null,
                };

                // Crear nuevo satélite
                $satelliteData = array_merge($data, ['activity_id' => $activity->id]);
                
                // Ensure fecha_programada is set for satellite
                if (!isset($satelliteData['fecha_programada'])) {
                    $satelliteData['fecha_programada'] = $data['fecha_inicio'] ?? $activity->fecha_inicio ?? now();
                }

                // No debemos excluir campos que son compartidos (program_id, nombre, etc.)
                // Solo excluimos campos de control interno o ids
                $excludeKeys = ['id', 'created_at', 'updated_at', 'tipo'];
                $satelliteDataFiltered = Arr::except($satelliteData, $excludeKeys);

                match ($newType) {
                    'inspeccion' => Inspection::create($satelliteDataFiltered),
                    'auditoria' => Audit::create($satelliteDataFiltered),
                    'capacitacion' => Training::create($satelliteDataFiltered),
                    'simulacro' => Drill::create($satelliteDataFiltered),
                    'incidente' => Incident::create($satelliteDataFiltered),
                    'comite' => Committee::create($satelliteDataFiltered),
                    'documentacion' => Documentation::create($satelliteDataFiltered),
                    'promocion' => Promotion::create($satelliteDataFiltered),
                    'control_operacional' => OperationalControl::create($satelliteDataFiltered),
                    default => null, 
                };
            } else {
                // Actualizar satélite existente
                $satellite = match ($newType) {
                    'inspeccion' => $activity->inspection,
                    'auditoria' => $activity->audit,
                    'capacitacion' => $activity->training,
                    'simulacro' => $activity->drill,
                    'incidente' => $activity->incident,
                    'comite' => $activity->committee,
                    'documentacion' => $activity->documentation,
                    'promocion' => $activity->promotion,
                    'control_operacional' => $activity->operationalControl,
                    default => null,
                };

                if ($satellite) {
                     // Solo excluimos campos de control, permitimos actualizar campos compartidos
                     $satelliteDataFiltered = Arr::except($data, ['activity_id', 'id', 'created_at', 'updated_at', 'tipo']);
                     $satellite->update($satelliteDataFiltered);
                } else {
                    // Si no existe (caso raro de inconsistencia), lo creamos
                    $satelliteData = array_merge($data, ['activity_id' => $activity->id]);
                    
                    // Ensure fecha_programada is set for satellite
                    if (!isset($satelliteData['fecha_programada'])) {
                        $satelliteData['fecha_programada'] = $data['fecha_inicio'] ?? $activity->fecha_inicio ?? now();
                    }

                    $satelliteDataFiltered = Arr::except($satelliteData, ['id', 'created_at', 'updated_at', 'tipo']);
                    
                    match ($newType) {
                        'inspeccion' => Inspection::create($satelliteDataFiltered),
                        'auditoria' => Audit::create($satelliteDataFiltered),
                        'capacitacion' => Training::create($satelliteDataFiltered),
                        'simulacro' => Drill::create($satelliteDataFiltered),
                        'incidente' => Incident::create($satelliteDataFiltered),
                        'comite' => Committee::create($satelliteDataFiltered),
                        'documentacion' => Documentation::create($satelliteDataFiltered),
                        'promocion' => Promotion::create($satelliteDataFiltered),
                        'control_operacional' => OperationalControl::create($satelliteDataFiltered),
                        default => null, 
                    };
                }
            }

            // 4. Recalcular próxima ejecución
            $this->generateExecutions($activity);
            $this->calculateNextExecution($activity);

            return $activity;
        });
    }

    /**
     * Actualiza una actividad y su registro satélite correspondiente.
     */
    public function updateWithType(Model $satellite, array $data): Model
    {
        return DB::transaction(function () use ($satellite, $data) {
            // 1. Actualizar el registro Satélite
            $satellite->update($data);

            // 2. Sincronizar campos comunes con la Actividad
            if ($satellite->activity) {
                $activityData = [
                    'nombre' => $data['nombre'] ?? $satellite->nombre,
                    'descripcion' => $data['descripcion'] ?? $satellite->descripcion ?? $data['nombre'] ?? $satellite->nombre,
                    'frecuencia' => $data['frecuencia'] ?? $satellite->frecuencia,
                    'fecha_inicio' => $data['fecha_programada'] ?? $satellite->fecha_programada,
                    'fecha_fin' => $data['fecha_programada'] ?? $satellite->fecha_programada,
                    'responsable_id' => $data['responsable_id'] ?? $satellite->responsable_id,
                    'veces_al_anio' => $data['veces_al_anio'] ?? $satellite->veces_al_anio ?? 1,
                    // Otros campos que se deban sincronizar
                ];

                // Filtrar nulos si es necesario, o dejar que update maneje
                // Aquí asumimos que si no viene en $data, mantenemos el valor actual del satélite (que ya fue actualizado)
                
                $satellite->activity->update($activityData);
                
                // Recalcular próxima ejecución si cambiaron fechas o frecuencia
                if (isset($data['fecha_programada']) || isset($data['frecuencia'])) {
                    $this->calculateNextExecution($satellite->activity);
                }
            }

            return $satellite;
        });
    }

    /**
     * Registra una ejecución de actividad, actualiza contadores y programa la siguiente.
     */
    public function registerExecution(Activity $activity, array $executionData): ActivityExecution
    {
        return DB::transaction(function () use ($activity, $executionData) {
            // 1. Crear la ejecución
            $execution = ActivityExecution::create([
                'activity_id' => $activity->id,
                'fecha_programada' => $executionData['fecha_programada'] ?? now(),
                'fecha_ejecucion_real' => $executionData['fecha_ejecucion_real'] ?? now(),
                'estado' => $executionData['estado'] ?? 'ejecutado',
                'observacion' => $executionData['observacion'] ?? null,
                // 'evidencia' se maneja por separado o aquí si se pasa
            ]);

            // 2. Actualizar contadores en Actividad
            $activity->increment('ejecuciones_realizadas');
            
            // 3. Actualizar estado de la actividad si es necesario
            // (Lógica simplificada, se puede expandir según reglas de negocio)
            if ($activity->ejecuciones_realizadas >= $activity->veces_al_anio) {
                $activity->update(['estado' => 'ejecutado']);
            } else {
                $activity->update(['estado' => 'en_proceso']);
            }

            // 4. Calcular siguiente fecha
            $this->calculateNextExecution($activity);

            return $execution;
        });
    }

    /**
     * Calcula y actualiza la fecha de próxima ejecución.
     * Centraliza la lógica que antes estaba dispersa.
     */
    /**
     * Genera las ejecuciones programadas basadas en la frecuencia y fecha de inicio.
     * Reemplaza la lógica anterior en Activity::regenerateExecutions.
     */
    public function generateExecutions(Activity $activity): void
    {
        if (!$activity->fecha_inicio || !$activity->frecuencia) {
            \Log::info("generateExecutions: No start date or frequency", ['activity_id' => $activity->id]);
            return;
        }

        // Delete future/pending/failed executions
        \Log::info("generateExecutions: Deleting existing executions for activity " . $activity->id);
        $deleted = $activity->executions()
             ->whereIn('estado', [
                 ActivityState::PROGRAMADO, 
                 'pendiente', 
                 ActivityState::NO_CUMPLIO, 
                 'vencido'
             ])
             ->delete();
        \Log::info("generateExecutions: Deleted $deleted executions");

        if ($activity->frecuencia === 'eventual') {
            if ($activity->executions()->count() === 0) {
                 $activity->executions()->create([
                    'fecha_programada' => $activity->fecha_inicio,
                    'estado' => ActivityState::PROGRAMADO,
                ]);
            }
            return;
        }

        $dates = [];
        $current = Carbon::parse($activity->fecha_inicio);
        $veces = (int) ($activity->veces_al_anio ?? 1);
        
        \Log::info("generateExecutions: Start", [
            'activity_id' => $activity->id,
            'frecuencia' => $activity->frecuencia,
            'veces' => $veces,
            'fecha_inicio' => $activity->fecha_inicio,
        ]);
        
        if ($veces > 366) $veces = 366;

        for ($i = 0; $i < $veces; $i++) {
            $dates[] = $current->copy();
            
            switch ($activity->frecuencia) {
                case 'diario': $current->addDay(); break;
                case 'semanal': $current->addWeek(); break;
                case 'mensual': $current->addMonth(); break;
                case 'trimestral': $current->addMonths(3); break;
                case 'semestral': $current->addMonths(6); break;
                case 'anual': $current->addYear(); break;
                default: $current->addMonth();
            }
        }

        foreach ($dates as $date) {
            $exists = $activity->executions()
                           ->whereDate('fecha_programada', $date)
                           ->exists();

            if (!$exists) {
                $created = $activity->executions()->create([
                    'fecha_programada' => $date,
                    'estado' => ActivityState::PROGRAMADO,
                ]);
                \Log::info("generateExecutions: Created execution", ['id' => $created->id]);
            } else {
                \Log::info("generateExecutions: Execution exists for date", ['date' => $date]);
            }
        }
    }

    /**
     * Calcula la próxima ejecución basada en las ejecuciones programadas pendientes.
     * Hallazgo 01: Migrar lógica para basarse en ActivityExecution.
     */
    public function calculateNextExecution(Activity $activity): void
    {
        // Buscar la próxima ejecución pendiente ordenada por fecha
        $nextExecution = $activity->executions()
            ->whereIn('estado', [ActivityState::PROGRAMADO, 'pendiente', 'vencido'])
            ->orderBy('fecha_programada', 'asc')
            ->first();

        if ($nextExecution) {
            $activity->update(['proxima_ejecucion' => $nextExecution->fecha_programada]);
        } else {
             // Si no hay ejecuciones pendientes, verificar si es eventual o si ya terminó todo
             if ($activity->frecuencia === 'eventual') {
                 $activity->update(['proxima_ejecucion' => null]);
             } else {
                 // Podríamos generar más ejecuciones aquí si fuera recurrente infinita, 
                 // pero por ahora asumimos recurrencia finita (veces_al_anio).
                 $activity->update(['proxima_ejecucion' => null]);
             }
        }
    }

    /**
     * Elimina una ejecución de actividad y recalcula la próxima fecha.
     * Centraliza la lógica de eliminación para asegurar la consistencia de proyecciones.
     */
    public function deleteExecution(ActivityExecution $execution): void
    {
        DB::transaction(function () use ($execution) {
            $activity = $execution->activity;
            
            // Eliminar la ejecución (SoftDelete si aplica, o forceDelete si se requiere, 
            // pero el modelo usa SoftDeletes por lo que delete() es suficiente)
            $execution->delete();

            // Recalcular la próxima ejecución en la actividad padre
            if ($activity) {
                $this->calculateNextExecution($activity);
            }
        });
    }
}
