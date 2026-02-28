<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Activity;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;

class HseDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Definir Roles y Usuarios necesarios (Normalizar nombres)
        $roles = [
            'JEFE QHSE' => 'jefe_qhse@hse.com',
            'Médico Ocupacional' => 'medico_ocupacional@hse.com',
            'Supervisor QHSE' => 'supervisor_qhse@hse.com',
            'Asistente QHSE' => 'asistente_qhse@hse.com',
            'Asistente General' => 'asistente_general@hse.com',
            'Monitor de Seguridad' => 'monitor_seguridad@hse.com',
        ];

        $users = [];
        foreach ($roles as $roleName => $email) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $roleName,
                    'password' => bcrypt('password'),
                    'role_id' => $role->id,
                ]
            );
            $users[$roleName] = $user->id;
            // Handle variations/typos in markdown
            if ($roleName === 'Médico Ocupacional') {
                $users['Medico Ocupacional'] = $user->id;
            }
        }

        // 2. Definir Datos de Programas y Actividades
        $programsData = [
            [
                'nombre' => '2.- Evaluación y Seguimiento',
                'descripcion' => 'Evaluar el desempeño de seguridad y salud ocupacional.',
                'activities' => [
                    ['nombre' => '2.1 Realizar Reuniones del Comité de Seguridad y Salud Ocupacional (Actas del CSST)', 'meta' => 100, 'frecuencia' => 'mensual', 'tipo' => 'comite', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '2.2 Implementación de Corrección , Acciones Correctivas y Acciones Preventivas', 'meta' => 100, 'frecuencia' => 'unico', 'tipo' => 'general', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '2.3 Monitoreos Ocupacionales', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'control_operacional', 'responsable' => 'Medico Ocupacional'],
                    ['nombre' => '2.4 Auditoría Interna QHSE', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'auditoria', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '2.5 Auditoría Externa en SST MINTRA', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'auditoria', 'responsable' => 'JEFE QHSE'],
                ]
            ],
            [
                'nombre' => '3.- Inspecciones Planeadas',
                'descripcion' => 'Desarrollar acciones para prevenir y para reducir al mínimo los factores de riesgo de SST y de los posibles Impactos Ambientales generados durante la ejecución de las actividades.',
                'activities' => [
                    ['nombre' => '3.1 Inspección General QHSE', 'meta' => 100, 'frecuencia' => 'trimestral', 'tipo' => 'inspeccion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '3.2 Inspección SSOMA', 'meta' => 100, 'frecuencia' => 'trimestral', 'tipo' => 'inspeccion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '3.3 Inspección de Oficinas', 'meta' => 100, 'frecuencia' => 'trimestral', 'tipo' => 'inspeccion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '3.4 Inspección de Luces de Emergencia y Alarmas', 'meta' => 100, 'frecuencia' => 'trimestral', 'tipo' => 'inspeccion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '3.5 Inspección de Botiquín de Primeros Auxilios', 'meta' => 100, 'frecuencia' => 'trimestral', 'tipo' => 'inspeccion', 'responsable' => 'Medico Ocupacional'],
                    ['nombre' => '3.6 Inspección de Equipos y Herramientas', 'meta' => 100, 'frecuencia' => 'trimestral', 'tipo' => 'inspeccion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '3.7 Inspección de Extintores', 'meta' => 100, 'frecuencia' => 'trimestral', 'tipo' => 'inspeccion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '3.8 Inspección de Equipos de Protección Personal.', 'meta' => 100, 'frecuencia' => 'trimestral', 'tipo' => 'inspeccion', 'responsable' => 'JEFE QHSE'],
                ]
            ],
            [
                'nombre' => '4.- Análisis de Riesgos y Trabajo Seguro',
                'descripcion' => 'Análisis y prevención de riesgos en el trabajo.',
                'activities' => [
                    ['nombre' => '4.1 Elaboración y Revisión de los Procedimientos de Trabajo', 'meta' => 100, 'frecuencia' => 'unico', 'tipo' => 'documentacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '4.2 Verificar el llenado Análisis de Trabajo Seguro', 'meta' => 100, 'frecuencia' => 'mensual', 'tipo' => 'inspeccion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '4.3 Entrega o Cambio de Equipos de Protección Personal', 'meta' => 100, 'frecuencia' => 'unico', 'tipo' => 'general', 'responsable' => 'JEFE QHSE'],
                ]
            ],
            [
                'nombre' => '5.-Capacitación y Entrenamiento',
                'descripcion' => 'Ejecutar las capacitaciones según el programa de capacitaciones de seguridad, salud ocupacional y medio ambiente.',
                'activities' => [
                    // A.-Entrenamiento
                    ['nombre' => 'A.1 Inducción QHSE', 'meta' => 100, 'frecuencia' => 'unico', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'], // "Personal nuevo" mapped to cuando_requiera
                    ['nombre' => 'A.2 Reglamento Interno de Seguridad y Salud en el Trabajo', 'meta' => 100, 'frecuencia' => 'unico', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'A.3 Recomendaciones QHSE', 'meta' => 100, 'frecuencia' => 'unico', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'A.4 Política QHSE ECYTEL S.A.C.', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'A.5 Plan de Contingencias ECYTEL S.A.C', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'A.6 Correcto Llenado de Registros de seguridad', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'A.7 Sensibilización en Seguridad (Charlas de 5 minutos)', 'meta' => 100, 'frecuencia' => 'diario', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    // B.-Capacitaciones en Seguridad
                    ['nombre' => 'B.1 Seguridad de Trabajos en Altura', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'B.2 Prevención y Control en Amago de Incendio', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'B.3 Identificación de Peligros , Evaluación de Riesgo y Controles (IPERC)', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'B.4 Uso y Conservación de Epp´s', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'B.5 Seguridad en Trabajos Eléctricos', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    // C.-Capacitaciones en Salud Ocupacional
                    ['nombre' => 'C.1 Primeros Auxilios RCP Y Atragantamientos', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'Medico Ocupacional'],
                    ['nombre' => 'C.2 Ergonomía en el Trabajo', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'Medico Ocupacional'],
                    ['nombre' => 'C.4 Protección Contra la Radiación Solar', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'Medico Ocupacional'],
                    // D.-Capacitaciones en Medio Ambiente
                    ['nombre' => 'D.1 Cuidado del Medio Ambiente y Manejo de residuos Sólidos', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    // E.-Capacitaciones al Comite
                    ['nombre' => 'E.1 Inspecciones de Trabajo (Comite)', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'E.2 IPERC (Comite)', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'E.3 Investigación de Accidentes y/o Incidentes (Comite)', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    // F.-Capacitaciones Específicas
                    ['nombre' => 'F.1 Seguridad vial y Manejo Defensivo', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => 'F.2 Procedimientos de Trabajo Seguro', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'capacitacion', 'responsable' => 'JEFE QHSE'],
                ]
            ],
            [
                'nombre' => '6.-Preparación ante Emergencias',
                'descripcion' => 'Implementar Simulacros para dar una respuesta lo ante posibles en situaciones de emergencia.',
                'activities' => [
                    ['nombre' => '6.1 Simulacro de Primeros Auxilios', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'simulacro', 'responsable' => 'Medico Ocupacional'],
                    ['nombre' => '6.2 Simulacro de actuación en caso de Accidente de Trabajo', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'simulacro', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '6.3 Simulacro de respuesta ante un Sismo', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'simulacro', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '6.4 Simulacro de Amago de Incendio', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'simulacro', 'responsable' => 'JEFE QHSE'],
                ]
            ],
            [
                'nombre' => '7. Gestión de Incidentes , Accidentes y Enfermedades Ocupacionales',
                'descripcion' => 'Evaluación de Accidentabilidad en Seguridad , Salud Ocupacional Y Medio Ambiente',
                'activities' => [
                    ['nombre' => '7.1 Investigacion y Reporte de Incidentes , Accidentes y Enfermedades Ocupacionales', 'meta' => 100, 'frecuencia' => 'unico', 'tipo' => 'general', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '7.2 Estadisticas de Accidentabilidad , Indicadores de Riesgos.', 'meta' => 100, 'frecuencia' => 'mensual', 'tipo' => 'documentacion', 'responsable' => 'JEFE QHSE'],
                ]
            ],
            [
                'nombre' => '8.-Promoción Y Motivación',
                'descripcion' => 'Motivación al personal en Materia de Seguridad , Salud Ocupacional Y Medio Ambiente.',
                'activities' => [
                    ['nombre' => '8.1 Premiación al Personal Relacionados a QHSE', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'promocion', 'responsable' => 'JEFE QHSE'],
                    ['nombre' => '8.2 Reconocimiento al Desempeño de Colaboradores en QHSE', 'meta' => 100, 'frecuencia' => 'anual', 'tipo' => 'promocion', 'responsable' => 'JEFE QHSE'],
                ]
            ],
        ];

        foreach ($programsData as $progData) {
            $program = Program::updateOrCreate(
                ['nombre' => $progData['nombre']],
                [
                    'descripcion' => $progData['descripcion'],
                    'anio' => 2026,
                    'estado' => 'aprobado', // Changed from 'activo' to 'aprobado'
                ]
            );

            foreach ($progData['activities'] as $actData) {
                // Calcular fechas estimadas basadas en frecuencia
                $fechaInicio = Carbon::create(2026, 1, 1);
                $fechaFin = Carbon::create(2026, 12, 31);
                
                if ($actData['frecuencia'] === 'mensual') {
                    $fechaFin = $fechaInicio->copy()->addMonth(); // Solo como ejemplo inicial
                } elseif ($actData['frecuencia'] === 'trimestral') {
                    $fechaFin = $fechaInicio->copy()->addMonths(3);
                } elseif ($actData['frecuencia'] === 'diario') {
                    $fechaFin = $fechaInicio->copy()->addDay();
                }

                $activity = Activity::updateOrCreate(
                    [
                        'program_id' => $program->id,
                        'nombre' => $actData['nombre'],
                    ],
                    [
                        'tipo' => $actData['tipo'],
                        'meta' => $actData['meta'],
                        'frecuencia' => $actData['frecuencia'],
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'estado' => 'programado',
                        'es_obligatoria' => true,
                        'responsable_id' => $users[$actData['responsable']] ?? $users['JEFE QHSE'],
                        'descripcion' => $actData['nombre'] . ' - ' . $program->nombre,
                    ]
                );

                // Crear el registro hijo correspondiente (reusing logic from CreateActivity)
                $this->createChildRecord($activity, $actData['tipo']);
            }
        }
    }

    private function createChildRecord(Activity $activity, string $tipo)
    {
        // Verificar si ya existe para no duplicar
        if ($tipo === 'auditoria' && !\App\Models\Audit::where('activity_id', $activity->id)->exists()) {
             \App\Models\Audit::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre' => $activity->nombre,
                'fecha_programada' => $activity->fecha_inicio,
                'estado' => 'programado',
                'auditor_id' => $activity->responsable_id,
            ]);
        } elseif ($tipo === 'inspeccion' && !\App\Models\Inspection::where('activity_id', $activity->id)->exists()) {
            \App\Models\Inspection::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre' => $activity->nombre,
                'fecha_programada' => $activity->fecha_inicio,
                'estado' => 'programado', // Changed from 'pendiente' (invalid enum?) to 'programado'
                'responsable_id' => $activity->responsable_id,
            ]);
        } elseif ($tipo === 'capacitacion' && !\App\Models\Training::where('activity_id', $activity->id)->exists()) {
            \App\Models\Training::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'tema' => $activity->nombre,
                'fecha_programada' => $activity->fecha_inicio,
                'estado' => 'programado',
                'responsable_id' => $activity->responsable_id,
            ]);
        } elseif ($tipo === 'simulacro' && !\App\Models\Drill::where('activity_id', $activity->id)->exists()) {
            \App\Models\Drill::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre' => $activity->nombre,
                'descripcion' => $activity->descripcion,
                'fecha_programada' => $activity->fecha_inicio,
                'estado' => 'programado',
            ]);
        } elseif ($tipo === 'incidente' && !\App\Models\Incident::where('activity_id', $activity->id)->exists()) {
             // Incidents usually aren't pre-created as "programado", but if the activity exists...
             // Maybe skip child creation for incidents as they are reactive?
             // But the user wants consistency. Let's create an "open" incident placeholder?
             // No, "Investigación" activity implies an incident happened.
             // If I create an Incident record, it shows up in Incidents table.
             // Let's create it as 'abierto' if needed, or skip.
             // Given 7.1 is "Cuando Ocurra", maybe we don't create the child yet?
             // But the prompt says "integrate... child record architecture".
             // I will create it.
            \App\Models\Incident::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'titulo' => $activity->nombre,
                'fecha_ocurrencia' => now(), // Placeholder
                'estado' => 'abierto',
                'responsable_id' => $activity->responsable_id,
            ]);
        } elseif ($tipo === 'comite' && !\App\Models\Committee::where('activity_id', $activity->id)->exists()) {
            \App\Models\Committee::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre' => $activity->nombre,
                'fecha_programada' => $activity->fecha_inicio,
                'estado' => 'programado',
                'responsable_id' => $activity->responsable_id,
            ]);
        } elseif ($tipo === 'documentacion' && !\App\Models\Documentation::where('activity_id', $activity->id)->exists()) {
            \App\Models\Documentation::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'titulo' => $activity->nombre,
                'tipo_documento' => 'otro',
                'version' => '1.0',
                'estado' => 'borrador',
                'responsable_id' => $activity->responsable_id,
            ]);
        } elseif ($tipo === 'promocion' && !\App\Models\Promotion::where('activity_id', $activity->id)->exists()) {
            \App\Models\Promotion::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre_campana' => $activity->nombre,
                'fecha_inicio' => $activity->fecha_inicio,
                'estado' => 'planificado',
                'responsable_id' => $activity->responsable_id,
            ]);
        } elseif ($tipo === 'control_operacional' && !\App\Models\OperationalControl::where('activity_id', $activity->id)->exists()) {
            \App\Models\OperationalControl::create([
                'program_id' => $activity->program_id,
                'activity_id' => $activity->id,
                'nombre_proceso' => $activity->nombre,
                'parametro' => 'General', // Default value
                'valor_esperado' => 'Cumplimiento', // Default value
                'fecha_programada' => $activity->fecha_inicio,
                'estado' => 'pendiente',
                'responsable_id' => $activity->responsable_id,
            ]);
        }
    }
}
