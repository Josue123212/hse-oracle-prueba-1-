<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        if ($record->tipo === 'auditoria' && $record->audit) {
            $data['audit_auditor_id'] = $record->audit->auditor_id;
            $data['audit_hallazgos'] = $record->audit->hallazgos;
        } elseif ($record->tipo === 'inspeccion' && $record->inspection) {
            $data['inspection_location_id'] = $record->inspection->location_id;
            $data['inspection_observaciones'] = $record->inspection->observaciones;
        } elseif ($record->tipo === 'capacitacion' && $record->training) {
            $data['training_tema'] = $record->training->tema;
            $data['training_hora_inicio'] = $record->training->hora_inicio;
            $data['training_duracion_horas'] = $record->training->duracion_horas;
            $data['training_asistentes_esperados'] = $record->training->asistentes_esperados;
        } elseif ($record->tipo === 'simulacro' && $record->drill) {
            $data['drill_escenario'] = $record->drill->escenario;
            $data['drill_participantes_count'] = $record->drill->participantes_count;
        } elseif ($record->tipo === 'incidente' && $record->incident) {
            $data['incident_lugar'] = $record->incident->lugar;
            $data['incident_severidad'] = $record->incident->severidad;
        } elseif ($record->tipo === 'comite' && $record->committee) {
            $data['committee_tema_principal'] = $record->committee->tema_principal;
        } elseif ($record->tipo === 'documentacion' && $record->documentation) {
            $data['documentation_tipo_documento'] = $record->documentation->tipo_documento;
            $data['documentation_version'] = $record->documentation->version;
            $data['documentation_archivo_path'] = $record->documentation->archivo_path;
        } elseif ($record->tipo === 'promocion' && $record->promotion) {
            $data['promotion_publico_objetivo'] = $record->promotion->publico_objetivo;
            $data['promotion_material_entregado'] = $record->promotion->material_entregado;
            $data['promotion_participantes_estimados'] = $record->promotion->participantes_estimados;
        } elseif ($record->tipo === 'control_operacional' && $record->operationalControl) {
            $data['operational_control_parametro'] = $record->operationalControl->parametro;
            $data['operational_control_valor_esperado'] = $record->operationalControl->valor_esperado;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
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

        $activityData = Arr::except($data, array_keys($extraFields));

        $oldType = $record->tipo;

        $record->update($activityData);

        // Si el tipo cambió, eliminar el registro hijo anterior para evitar duplicados en vistas
        if ($oldType !== $record->tipo) {
            match ($oldType) {
                'auditoria' => $record->audit()->delete(),
                'inspeccion' => $record->inspection()->delete(),
                'capacitacion' => $record->training()->delete(),
                'simulacro' => $record->drill()->delete(),
                'incidente' => $record->incident()->delete(),
                'comite' => $record->committee()->delete(),
                'documentacion' => $record->documentation()->delete(),
                'promocion' => $record->promotion()->delete(),
                'control_operacional' => $record->operationalControl()->delete(),
                default => null,
            };
        }

        // Actualizar registro hijo
        if ($record->tipo === 'auditoria') {
            $auditStatus = match($record->estado) {
                'programado' => 'programado',
                'ejecutado' => 'ejecutado',
                'vencido', 'no_cumplio' => 'vencido',
                'reprogramado' => 'reprogramado',
                default => 'programado',
            };
            $record->audit()->updateOrCreate(
                ['activity_id' => $record->id],
                [
                    'program_id' => $record->program_id,
                    'nombre' => $record->nombre,
                    'descripcion' => $record->descripcion,
                    'fecha_programada' => $record->fecha_inicio ?? now(),
                    'estado' => $auditStatus,
                    'auditor_id' => $extraFields['audit_auditor_id'] ?? null,
                    'hallazgos' => $extraFields['audit_hallazgos'] ?? null,
                ]
            );
        } elseif ($record->tipo === 'inspeccion') {
            $inspectionStatus = match($record->estado) {
                'programado' => 'programado',
                'ejecutado' => 'ejecutado',
                'vencido', 'no_cumplio' => 'vencido',
                'reprogramado' => 'reprogramado',
                default => 'programado',
            };
            $record->inspection()->updateOrCreate(
                ['activity_id' => $record->id],
                [
                    'program_id' => $record->program_id,
                    'nombre' => $record->nombre,
                    'descripcion' => $record->descripcion,
                    'fecha_programada' => $record->fecha_inicio ?? now(),
                    'estado' => $inspectionStatus,
                    'responsable_id' => $record->responsable_id,
                    'location_id' => $extraFields['inspection_location_id'] ?? null,
                    'observaciones' => $extraFields['inspection_observaciones'] ?? null,
                ]
            );
        } elseif ($record->tipo === 'capacitacion') {
            $trainingStatus = match($record->estado) {
                'programado' => 'programado',
                'ejecutado' => 'ejecutado',
                'vencido', 'no_cumplio' => 'cancelado',
                'reprogramado' => 'reprogramado',
                default => 'programado',
            };
            $record->training()->updateOrCreate(
                ['activity_id' => $record->id],
                [
                    'program_id' => $record->program_id,
                    'tema' => $extraFields['training_tema'] ?? $record->nombre,
                    'descripcion' => $record->descripcion,
                    'fecha_programada' => $record->fecha_inicio ?? now(),
                    'fecha_ejecucion' => $record->fecha_fin,
                    'hora_inicio' => $extraFields['training_hora_inicio'] ?? null,
                    'duracion_horas' => $extraFields['training_duracion_horas'] ?? 1,
                    'estado' => $trainingStatus,
                    'asistentes_esperados' => $extraFields['training_asistentes_esperados'] ?? 0,
                    'responsable_id' => $record->responsable_id,
                ]
            );
        } elseif ($record->tipo === 'simulacro') {
             $drillStatus = match($record->estado) {
                'programado' => 'programado',
                'ejecutado' => 'ejecutado',
                'vencido', 'no_cumplio' => 'cancelado',
                default => 'programado',
            };
            $record->drill()->updateOrCreate(
                ['activity_id' => $record->id],
                [
                    'program_id' => $record->program_id,
                    'nombre' => $record->nombre,
                    'descripcion' => $record->descripcion,
                    'fecha_programada' => $record->fecha_inicio ?? now(),
                    'fecha_ejecucion' => $record->fecha_fin,
                    'estado' => $drillStatus,
                    'escenario' => $extraFields['drill_escenario'] ?? null,
                    'participantes_count' => $extraFields['drill_participantes_count'] ?? 0,
                    'frecuencia' => $record->frecuencia,
                    'veces_al_anio' => $record->veces_al_anio,
                    'ejecuciones_realizadas' => $record->ejecuciones_realizadas,
                    'detalle_frecuencia' => $record->detalle_frecuencia,
                ]
            );
        } elseif ($record->tipo === 'incidente') {
            // Incidents don't map directly to activity status in the same way, but let's be safe or leave as is if no direct mapping needed.
            // Incidents have: abierto, investigacion, cerrado. Activity has: programado, ejecutado, vencido, no_cumplio, reprogramado.
            // It seems Incidents are triggered, not really "programado".
            // However, the code was creating them. Let's check constraints.
            // Constraint: ['abierto', 'investigacion', 'cerrado'] default 'abierto'.
            // The current code does NOT set 'estado' for incidents in updateOrCreate, so it uses default 'abierto' or keeps existing.
            // This is likely fine as incidents follow their own lifecycle.
            $record->incident()->updateOrCreate(
                ['activity_id' => $record->id],
                [
                    'program_id' => $record->program_id,
                    'titulo' => $record->nombre,
                    'descripcion' => $record->descripcion,
                    'fecha_ocurrencia' => $record->fecha_inicio ?? now(),
                    'lugar' => $extraFields['incident_lugar'] ?? null,
                    'severidad' => $extraFields['incident_severidad'] ?? 'leve',
                    'responsable_id' => $record->responsable_id,
                ]
            );
        } elseif ($record->tipo === 'comite') {
             $committeeStatus = match($record->estado) {
                'programado' => 'programado',
                'ejecutado' => 'realizado',
                'vencido', 'no_cumplio' => 'cancelado',
                default => 'programado',
            };
            $record->committee()->updateOrCreate(
                ['activity_id' => $record->id],
                [
                    'program_id' => $record->program_id,
                    'nombre' => $record->nombre,
                    'tema_principal' => $extraFields['committee_tema_principal'] ?? null,
                    'fecha_programada' => $record->fecha_inicio ?? now(),
                    'fecha_realizada' => $record->fecha_fin,
                    'estado' => $committeeStatus,
                    'responsable_id' => $record->responsable_id,
                    'frecuencia' => $record->frecuencia,
                    'veces_al_anio' => $record->veces_al_anio,
                    'ejecuciones_realizadas' => $record->ejecuciones_realizadas,
                    'detalle_frecuencia' => $record->detalle_frecuencia,
                ]
            );
        } elseif ($record->tipo === 'documentacion') {
             // Documentation: borrador, revision, aprobado, obsoleto.
             // No direct mapping from activity status seems appropriate or safe without forcing state.
             // Leaving as is (uses default or existing).
            $record->documentation()->updateOrCreate(
                ['activity_id' => $record->id],
                [
                    'program_id' => $record->program_id,
                    'titulo' => $record->nombre,
                    'tipo_documento' => $extraFields['documentation_tipo_documento'] ?? 'otro',
                    'descripcion' => $record->descripcion,
                    'version' => $extraFields['documentation_version'] ?? '1.0',
                    'archivo_path' => $extraFields['documentation_archivo_path'] ?? null,
                    'responsable_id' => $record->responsable_id,
                ]
            );
        } elseif ($record->tipo === 'promocion') {
             $promotionStatus = match($record->estado) {
                'programado' => 'planificado',
                'ejecutado' => 'finalizado',
                'vencido', 'no_cumplio' => 'cancelado',
                default => 'planificado',
            };
            $record->promotion()->updateOrCreate(
                ['activity_id' => $record->id],
                [
                    'program_id' => $record->program_id,
                    'nombre_campana' => $record->nombre,
                    'descripcion' => $record->descripcion,
                    'fecha_inicio' => $record->fecha_inicio ?? now(),
                    'fecha_fin' => $record->fecha_fin,
                    'estado' => $promotionStatus,
                    'publico_objetivo' => $extraFields['promotion_publico_objetivo'] ?? null,
                    'material_entregado' => $extraFields['promotion_material_entregado'] ?? null,
                    'participantes_estimados' => $extraFields['promotion_participantes_estimados'] ?? 0,
                    'responsable_id' => $record->responsable_id,
                ]
            );
        } elseif ($record->tipo === 'control_operacional') {
             // Operational Control: conforme, no_conforme, pendiente.
             // Mapping could be: programado -> pendiente.
             $operationalStatus = match($record->estado) {
                'programado' => 'pendiente',
                'ejecutado' => 'conforme', // Assumption, but might be risky. Better default to pending if unknown.
                'vencido', 'no_cumplio' => 'no_conforme',
                default => 'pendiente',
            };
            // Actually, operational control is often about measurement.
            // Let's stick to 'pendiente' for programado.
             $operationalStatus = match($record->estado) {
                'programado' => 'pendiente',
                'vencido', 'no_cumplio' => 'no_conforme',
                default => 'pendiente',
            };
            
            $record->operationalControl()->updateOrCreate(
                ['activity_id' => $record->id],
                [
                    'program_id' => $record->program_id,
                    'nombre_proceso' => $record->nombre,
                    'parametro' => $extraFields['operational_control_parametro'] ?? 'N/A',
                    'valor_esperado' => $extraFields['operational_control_valor_esperado'] ?? 'N/A',
                    'fecha_programada' => $record->fecha_inicio ?? now(),
                    // 'estado' => $operationalStatus, // Uncomment if we want to sync state
                    'responsable_id' => $record->responsable_id,
                    'frecuencia' => $record->frecuencia,
                    'veces_al_anio' => $record->veces_al_anio,
                    'ejecuciones_realizadas' => $record->ejecuciones_realizadas,
                    'detalle_frecuencia' => $record->detalle_frecuencia,
                ]
            );
        }

        return $record;
    }
}
