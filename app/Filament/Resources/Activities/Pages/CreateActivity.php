<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Campos específicos que no pertenecen a Activity
        $extraFields = Arr::only($data, [
            'audit_auditor_id', 'audit_hallazgos',
            'inspection_location_id', 'inspection_observaciones',
            'training_tema', 'training_hora_inicio', 'training_duracion_horas', 'training_asistentes_esperados',
            'drill_escenario', 'drill_participantes_count',
            'incident_lugar', 'incident_severidad',
            'committee_tema_principal',
            'documentation_tipo_documento', 'documentation_version', 'documentation_archivo_path',
            'promotion_publico_objetivo', 'promotion_material_entregado', 'promotion_participantes_estimados',
            'operational_control_parametro', 'operational_control_valor_esperado',
        ]);

        // Limpiar data para crear Activity
        $activityData = Arr::except($data, array_keys($extraFields));
        
        $activity = static::getModel()::create($activityData);

        // Crear registro hijo según el tipo
        if (($data['tipo'] ?? '') === 'auditoria') {
            \App\Models\Audit::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre' => $activity->nombre,
                'descripcion' => $activity->descripcion,
                'fecha_programada' => $activity->fecha_inicio ?? now(),
                'estado' => $activity->estado,
                'auditor_id' => $extraFields['audit_auditor_id'] ?? null,
                'hallazgos' => $extraFields['audit_hallazgos'] ?? null,
            ]);
        } elseif (($data['tipo'] ?? '') === 'inspeccion') {
            \App\Models\Inspection::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre' => $activity->nombre,
                'descripcion' => $activity->descripcion,
                'fecha_programada' => $activity->fecha_inicio ?? now(),
                'estado' => $activity->estado,
                'responsable_id' => $activity->responsable_id,
                'location_id' => $extraFields['inspection_location_id'] ?? null,
                'observaciones' => $extraFields['inspection_observaciones'] ?? null,
                'resultado' => 'pendiente', // Default
            ]);
        } elseif (($data['tipo'] ?? '') === 'capacitacion') {
            \App\Models\Training::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'tema' => $extraFields['training_tema'] ?? $activity->nombre,
                'descripcion' => $activity->descripcion,
                'fecha_programada' => $activity->fecha_inicio ?? now(),
                'fecha_ejecucion' => $activity->fecha_fin,
                'hora_inicio' => $extraFields['training_hora_inicio'] ?? null,
                'duracion_horas' => $extraFields['training_duracion_horas'] ?? 1,
                'estado' => $activity->estado,
                'asistentes_esperados' => $extraFields['training_asistentes_esperados'] ?? 0,
                'responsable_id' => $activity->responsable_id,
            ]);
        } elseif (($data['tipo'] ?? '') === 'simulacro') {
            $drillStatus = match($activity->estado) {
                'programado' => 'programado',
                'ejecutado' => 'ejecutado',
                'no_cumplio' => 'cancelado',
                default => 'programado',
            };
            \App\Models\Drill::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre' => $activity->nombre,
                'descripcion' => $activity->descripcion,
                'fecha_programada' => $activity->fecha_inicio ?? now(),
                'fecha_ejecucion' => $activity->fecha_fin,
                'estado' => $drillStatus,
                'escenario' => $extraFields['drill_escenario'] ?? null,
                'participantes_count' => $extraFields['drill_participantes_count'] ?? 0,
                'frecuencia' => $activity->frecuencia,
                'veces_al_anio' => $activity->veces_al_anio,
                'ejecuciones_realizadas' => $activity->ejecuciones_realizadas,
                'detalle_frecuencia' => $activity->detalle_frecuencia,
            ]);
        } elseif (($data['tipo'] ?? '') === 'incidente') {
            \App\Models\Incident::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'titulo' => $activity->nombre,
                'descripcion' => $activity->descripcion,
                'fecha_ocurrencia' => $activity->fecha_inicio ?? now(),
                'lugar' => $extraFields['incident_lugar'] ?? null,
                'severidad' => $extraFields['incident_severidad'] ?? 'leve',
                'estado' => 'abierto',
                'responsable_id' => $activity->responsable_id,
            ]);
        } elseif (($data['tipo'] ?? '') === 'comite') {
            $committeeStatus = match($activity->estado) {
                'programado' => 'programado',
                'ejecutado' => 'realizado',
                'no_cumplio' => 'cancelado',
                default => 'programado',
            };
            \App\Models\Committee::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre' => $activity->nombre,
                'tema_principal' => $extraFields['committee_tema_principal'] ?? null,
                'fecha_programada' => $activity->fecha_inicio ?? now(),
                'fecha_realizada' => $activity->fecha_fin,
                'estado' => $committeeStatus,
                'responsable_id' => $activity->responsable_id,
                'frecuencia' => $activity->frecuencia,
                'veces_al_anio' => $activity->veces_al_anio,
                'ejecuciones_realizadas' => $activity->ejecuciones_realizadas,
                'detalle_frecuencia' => $activity->detalle_frecuencia,
            ]);
        } elseif (($data['tipo'] ?? '') === 'documentacion') {
            \App\Models\Documentation::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'titulo' => $activity->nombre,
                'tipo_documento' => $extraFields['documentation_tipo_documento'] ?? 'otro',
                'descripcion' => $activity->descripcion,
                'version' => $extraFields['documentation_version'] ?? '1.0',
                'estado' => 'borrador',
                'archivo_path' => $extraFields['documentation_archivo_path'] ?? null,
                'responsable_id' => $activity->responsable_id,
            ]);
        } elseif (($data['tipo'] ?? '') === 'promocion') {
            $promotionStatus = match($activity->estado) {
                'programado' => 'planificado',
                'ejecutado' => 'finalizado',
                'no_cumplio' => 'cancelado',
                default => 'planificado',
            };
            \App\Models\Promotion::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre_campana' => $activity->nombre,
                'descripcion' => $activity->descripcion,
                'fecha_inicio' => $activity->fecha_inicio ?? now(),
                'fecha_fin' => $activity->fecha_fin,
                'estado' => $promotionStatus,
                'publico_objetivo' => $extraFields['promotion_publico_objetivo'] ?? null,
                'material_entregado' => $extraFields['promotion_material_entregado'] ?? null,
                'participantes_estimados' => $extraFields['promotion_participantes_estimados'] ?? 0,
                'responsable_id' => $activity->responsable_id,
            ]);
        } elseif (($data['tipo'] ?? '') === 'control_operacional') {
            \App\Models\OperationalControl::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre_proceso' => $activity->nombre,
                'parametro' => $extraFields['operational_control_parametro'] ?? 'N/A',
                'valor_esperado' => $extraFields['operational_control_valor_esperado'] ?? 'N/A',
                'fecha_programada' => $activity->fecha_inicio ?? now(),
                'estado' => 'pendiente',
                'responsable_id' => $activity->responsable_id,
                'frecuencia' => $activity->frecuencia,
                'veces_al_anio' => $activity->veces_al_anio,
                'ejecuciones_realizadas' => $activity->ejecuciones_realizadas,
                'detalle_frecuencia' => $activity->detalle_frecuencia,
            ]);
        }

        return $activity;
    }
}
