<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\User;
use App\Models\Role;
use App\Models\Training;
use App\Models\Drill;
use App\Models\Incident;
use App\Models\Committee;
use App\Models\Documentation;
use App\Models\Promotion;
use App\Models\OperationalControl;
use App\Models\Inspection;
use App\Models\Audit;
use App\Models\Activity;
use App\Models\Location;
use Carbon\Carbon;

class CompleteHseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar Roles y Usuarios
        $this->ensureRolesAndUsers();

        // Asegurar Sedes
        $arequipa = Location::firstOrCreate([
            'nombre' => 'Base Arequipa',
            'direccion' => 'Arequipa',
            'activo' => true
        ]);

        $frentes = Location::firstOrCreate([
            'nombre' => 'Frentes de Trabajo',
            'direccion' => 'Varios',
            'activo' => true
        ]);

        $oficina = Location::firstOrCreate([
            'nombre' => 'Oficina Administrativa',
            'direccion' => 'Oficina Principal',
            'activo' => true
        ]);

        // 2. Crear Programas y sus Actividades usando la Metodología Maestro-Detalle
        $this->seedProgram1($arequipa); // Liderazgo y Compromiso (Anual) - Todo Arequipa
        $this->seedProgram2($arequipa); // Evaluación y Seguimiento (Mensual, Anual) - Todo Arequipa
        $this->seedProgram3($arequipa, $frentes); // Inspecciones Planeadas (Trimestral) - Mixto
        $this->seedProgram4($frentes); // Análisis de Riesgos (Mensual, Cuando se requiera) - Todo Frentes
        $this->seedProgram5($arequipa, $frentes, $oficina); // Capacitación - Mixto
        $this->seedProgram6($frentes); // Emergencias (Anual) - Todo Frentes
        $this->seedProgram7($frentes); // Incidentes (Mensual, Cuando ocurra) - Todo Frentes
        $this->seedProgram8($frentes); // Promoción (Anual) - Todo Frentes
    }

    private function ensureRolesAndUsers()
    {
        $roles = [
            'JEFE QHSE' => 'jefe_qhse@hse.com',
            'Médico Ocupacional' => 'medico_ocupacional@hse.com',
            'SUPERVISOR QHSE' => 'supervisor_qhse@hse.com',
            'Asistente QHSE' => 'asistente_qhse@hse.com',
            'Asistente General' => 'asistente_general@hse.com',
            'Jefes de Área' => 'jefes_area@hse.com',
            'Monitor de Seguridad' => 'monitor_seguridad@hse.com',
        ];

        foreach ($roles as $roleName => $email) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $roleName,
                    'password' => bcrypt('password'),
                    'role_id' => $role->id,
                ]
            );
        }
    }

    private function getUserId($roleName)
    {
        $user = User::where('name', $roleName)->first();
        return $user ? $user->id : User::first()->id;
    }

    /**
     * MOTOR DE GENERACIÓN DE ACTIVIDADES RECURRENTES
     */
    private function createRecurringActivity(
        $program,
        $location,
        $title,
        $frequency,
        $responsableRole,
        $relatedModelClass = null,
        $relatedData = [],
        $meta = 100
    ) {
        $responsableId = $this->getUserId($responsableRole);

        $validFrequencies = ['diario', 'semanal', 'mensual', 'trimestral', 'semestral', 'anual', 'unico'];
        $dbFrequency = strtolower($frequency);
        if (!in_array($dbFrequency, $validFrequencies)) {
            $dbFrequency = 'unico';
        }

        // Determine tipo based on related model
        $tipo = 'general';
        if ($relatedModelClass) {
            $tipo = match ($relatedModelClass) {
                Inspection::class => 'inspeccion',
                Audit::class => 'auditoria',
                Training::class => 'capacitacion',
                Drill::class => 'simulacro',
                Incident::class => 'incidente',
                Committee::class => 'comite',
                Documentation::class => 'documentacion',
                Promotion::class => 'promocion',
                OperationalControl::class => 'control_operacional',
                default => 'general',
            };
        }

        // Calcular número de repeticiones al año
        $vecesAlAnio = 1;
        switch (strtolower($frequency)) {
            case 'diario':
                $vecesAlAnio = 365;
                break;
            case 'semanal':
                $vecesAlAnio = 52;
                break;
            case 'mensual':
                $vecesAlAnio = 12;
                break;
            case 'trimestral':
                $vecesAlAnio = 4;
                break;
            case 'semestral':
                $vecesAlAnio = 2;
                break;
            case 'anual':
                $vecesAlAnio = 1;
                break;
            case 'cuando se requiera':
            case 'cuando ocurra':
            case 'personal nuevo':
            case 'personal nuevo o reasignado':
                $vecesAlAnio = 0; // Indefinido / A demanda
                break;
            default:
                $vecesAlAnio = 1;
        }

        // 1. Crear Actividad MAESTRA (Única Fila)
        $masterActivity = Activity::create([
            'program_id' => $program->id,
            'location_id' => $location->id,
            'nombre' => $title,
            'descripcion' => "Actividad Maestra: $title ($frequency)",
            'es_plantilla' => true,
            'frecuencia' => $dbFrequency,
            'veces_al_anio' => $vecesAlAnio, // Columna renombrada
            'tipo' => $tipo,
            'meta' => $meta,
            'es_obligatoria' => true,
            'responsable_id' => $responsableId,
            'estado' => 'programado', // Estado general
        ]);

        // 2. Crear registro en tabla relacionada (Vinculado a la Maestra como configuración base)
        if ($relatedModelClass) {
            $data = $relatedData;
            $data['program_id'] = $program->id;
            $data['activity_id'] = $masterActivity->id; // VINCULACIÓN DIRECTA A LA MAESTRA
            $data['responsable_id'] = $responsableId;
            
            // Ajustar fechas genéricas si es necesario
            if (isset($data['fecha_programada']) && $data['fecha_programada'] == '2026-01-01') {
                // Mantener fecha base, o dejar nulo si se prefiere no tener fecha específica
            }

            // Campos específicos dinámicos
            if ($relatedModelClass == Committee::class) {
                $data['nombre'] = $masterActivity->nombre;
            }
            if ($relatedModelClass == Documentation::class) {
                $data['titulo'] = $masterActivity->nombre;
            }
            if ($relatedModelClass == Inspection::class || $relatedModelClass == Audit::class || $relatedModelClass == Drill::class) {
                $data['nombre'] = $masterActivity->nombre;
            }
            if ($relatedModelClass == Training::class) {
                $data['tema'] = $title;
            }

            $relatedModelClass::create($data);
        }
    }

    private function getMonthName($m)
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $months[$m] ?? '';
    }

    // --- PROGRAM SEEDERS ---

    private function seedProgram1($location)
    {
        $program = Program::firstOrCreate(['nombre' => '1. Liderazgo y Compromiso'], ['anio' => 2026, 'estado' => 'aprobado']);
        
        $activities = [
            ['1.1 Elaboración y Aprobación del Plan Anual QHSE 2026', 'Anual', 'JEFE QHSE'],
            ['1.2 Elaboración y Aprobación del Programa Anual QHSE 2026', 'Anual', 'JEFE QHSE'],
            ['1.3 Definición de los Objetivos y Metas de QHSE Cymtel SAC 2026', 'Anual', 'JEFE QHSE'],
            ['1.4 Definición del Alcance de QHSE ECYTEL SAC 2026', 'Anual', 'JEFE QHSE'],
            ['1.5 Difusión de la Política en QHSE de ECYTEL SAC y Clientes', 'Anual', 'JEFE QHSE'],
            ['1.6 Actualizar la Matriz IPERC', 'Anual', 'JEFE QHSE'],
            ['1.7 Actualizar la Matriz IAAS', 'Anual', 'JEFE QHSE'],
            ['1.8 Actualizar la Matriz de Monitoreo de Requisitos Legales QHSE ECYTEL SAC 2026', 'Anual', 'JEFE QHSE'],
            ['1.9 Elaboración y Actualización de Mapas de Riesgos y Rutas de Evacuación', 'Anual', 'JEFE QHSE'],
            ['1.10 Elaboración de Plan Anual de Salud Ocupacional ECYTEL SAC 2026', 'Anual', 'Médico Ocupacional'],
            ['1.11 Elaboración de Programa Anual de Salud Ocupacional Cymtel SAC 2026', 'Anual', 'Médico Ocupacional'],
            ['1.12 Elaboración de Plan Anual de Manejo Ambiental ECYTEL SAC 2026', 'Anual', 'JEFE QHSE'],
            ['1.13 Elaboración de Plan Anual de Control y Seguimiento Vehícular Cymtel SAC 2026', 'Anual', 'JEFE QHSE'],
            ['1.14 Elaboración del Plan de Contingencias ECYTEL SAC 2026', 'Anual', 'JEFE QHSE'],
        ];

        foreach ($activities as $act) {
            $this->createRecurringActivity($program, $location, $act[0], $act[1], $act[2], Documentation::class, [
                'tipo_documento' => 'Plan/Programa',
                'estado' => 'aprobado'
            ]);
        }
    }

    private function seedProgram2($location)
    {
        $program = Program::firstOrCreate(['nombre' => '2. Evaluación y Seguimiento'], ['anio' => 2026, 'estado' => 'aprobado']);

        // 2.1 Mensual
        $this->createRecurringActivity($program, $location, '2.1 Realizar Reuniones del CSST', 'Mensual', 'JEFE QHSE', Committee::class, [
            'tema_principal' => 'Revisión del Sistema de Gestión',
            'fecha_programada' => '2026-01-01', // Se sobrescribe
            'estado' => 'programado'
        ]);

        // 2.2 Cuando se requiera
        $this->createRecurringActivity($program, $location, '2.2 Implementación de Acciones Correctivas', 'Cuando se requiera', 'JEFE QHSE', OperationalControl::class, [
            'nombre_proceso' => 'Gestión de No Conformidades',
            'parametro' => 'Cierre de NC',
            'valor_esperado' => '100%',
            'fecha_programada' => '2026-01-01'
        ]);

        // 2.3 Anual
        $this->createRecurringActivity($program, $location, '2.3 Monitoreos Ocupacionales', 'Anual', 'Médico Ocupacional', OperationalControl::class, [
            'nombre_proceso' => 'Monitoreo de Agentes',
            'parametro' => 'Límites Permisibles',
            'valor_esperado' => 'Cumplimiento Normativo',
            'fecha_programada' => '2026-10-01'
        ]);

        // 2.4 Anual
        $this->createRecurringActivity($program, $location, '2.4 Auditoría Interna QHSE', 'Anual', 'JEFE QHSE', Audit::class, [
            'tipo_auditoria' => 'interna',
            'entidad_auditora' => 'Interno',
            'alcance' => 'Sistema Integrado',
            'fecha_programada' => '2026-08-01'
        ]);

        // 2.5 Anual
        $this->createRecurringActivity($program, $location, '2.5 Auditoría Externa en SST MINTRA', 'Anual', 'JEFE QHSE', Audit::class, [
            'tipo_auditoria' => 'externa',
            'entidad_auditora' => 'MINTRA',
            'alcance' => 'Cumplimiento Legal',
            'fecha_programada' => '2026-11-01'
        ]);
    }

    private function seedProgram3($arequipa, $frentes)
    {
        $program = Program::firstOrCreate(['nombre' => '3. Inspecciones Planeadas'], ['anio' => 2026, 'estado' => 'aprobado']);

        $inspections = [
            '3.1 Inspección General QHSE' => $arequipa,
            '3.2 Inspección SSOMA' => $arequipa,
            '3.3 Inspección de Oficinas' => $arequipa,
            '3.4 Inspección de Luces de Emergencia y Alarmas' => $arequipa,
            '3.5 Inspección de Botiquín de Primeros Auxilios' => $frentes,
            '3.6 Inspección de Equipos y Herramientas' => $frentes,
            '3.7 Inspección de Extintores' => $frentes,
            '3.8 Inspección de Equipos de Protección Personal' => $frentes
        ];

        foreach ($inspections as $inspName => $location) {
            $this->createRecurringActivity($program, $location, $inspName, 'Trimestral', 'JEFE QHSE', Inspection::class, [
                'fecha_programada' => '2026-01-01', // Se sobrescribe
                'lugar' => $location->nombre,
                'tipo_inspeccion' => 'Planeada',
                'frecuencia' => 'Trimestral'
            ]);
        }
    }

    private function seedProgram4($location)
    {
        $program = Program::firstOrCreate(['nombre' => '4. Análisis de Riesgos y Trabajo Seguro'], ['anio' => 2026, 'estado' => 'aprobado']);

        $this->createRecurringActivity($program, $location, '4.1 Elaboración de Procedimientos de Trabajo', 'Cuando se requiera', 'JEFE QHSE', Documentation::class, [
            'tipo_documento' => 'Procedimiento',
            'titulo' => 'Procedimiento Específico'
        ]);

        $this->createRecurringActivity($program, $location, '4.2 Verificar llenado ATS', 'Mensual', 'JEFE QHSE', OperationalControl::class, [
            'nombre_proceso' => 'Control Operacional ATS',
            'parametro' => 'Calidad de Llenado',
            'valor_esperado' => '100% Correcto',
            'fecha_programada' => '2026-01-01'
        ]);

        $this->createRecurringActivity($program, $location, '4.3 Entrega o Cambio de EPP', 'Cuando se requiera', 'JEFE QHSE', OperationalControl::class, [
            'nombre_proceso' => 'Gestión de EPP',
            'parametro' => 'Entrega Oportuna',
            'valor_esperado' => '100%',
            'fecha_programada' => '2026-01-01'
        ]);
    }

    private function seedProgram5($arequipa, $frentes, $oficina)
    {
        $parentProgram = Program::firstOrCreate(['nombre' => '5. Capacitación y Entrenamiento'], ['anio' => 2026, 'estado' => 'aprobado']);

        // Estructura Jerárquica de Sub-programas
        $structure = [
            'A. Entrenamiento' => [
                ['5.A.1 Inducción QHSE', 'Personal nuevo'],
                ['5.A.2 Reglamento Interno', 'Personal nuevo'],
                ['5.A.3 Recomendaciones QHSE', 'Personal nuevo'],
                ['5.A.4 Política QHSE', 'Anual'],
                ['5.A.5 Plan de Contingencias', 'Anual'],
                ['5.A.6 Correcto Llenado de Registros', 'Anual'],
                ['5.A.7 Sensibilización (Charlas 5 min)', 'Diario'], // ¡OJO! Generará muchos registros
            ],
            'B. Capacitaciones en Seguridad' => [
                ['5.B.1 Trabajos en Altura', 'Anual'],
                ['5.B.2 Amago de Incendio', 'Anual'],
                ['5.B.3 IPERC', 'Anual'],
                ['5.B.4 Uso de EPPs', 'Anual'],
                ['5.B.5 Riesgo Eléctrico', 'Anual'],
            ],
            'C. Salud Ocupacional' => [
                ['5.C.1 Primeros Auxilios', 'Anual'],
                ['5.C.2 Ergonomía', 'Anual'],
                ['5.C.4 Radiación Solar', 'Anual'],
            ],
            'D. Medio Ambiente' => [
                ['5.D.1 Manejo de Residuos', 'Anual'],
            ],
            'E. Comité SST' => [
                ['5.E.1 Inspecciones', 'Anual'],
                ['5.E.2 IPERC (Comité)', 'Anual'],
                ['5.E.3 Investigación Accidentes', 'Anual'],
            ],
            'F. Específicas' => [
                ['5.F.1 Manejo Defensivo', 'Anual'],
                ['5.F.2 Procedimientos Seguros', 'Anual'],
            ],
        ];

        foreach ($structure as $subName => $trainings) {
            $subProgram = Program::firstOrCreate(
                ['nombre' => $subName, 'parent_id' => $parentProgram->id],
                ['anio' => 2026, 'estado' => 'aprobado']
            );

            foreach ($trainings as $t) {
                // Lógica de Sede
                $location = $frentes; // Por defecto
                if (str_contains($t[0], '5.A.4') || str_contains($t[0], '5.C.4')) {
                    $location = $arequipa;
                } elseif (str_contains($t[0], '5.C.2')) {
                    $location = $oficina;
                }

                $this->createRecurringActivity($subProgram, $location, $t[0], $t[1], 'JEFE QHSE', Training::class, [
                    'tema' => $t[0],
                    'fecha_programada' => '2026-01-01',
                    'duracion_horas' => ($t[1] == 'Diario' ? 0.08 : 2), // 5 min o 2 horas
                    'estado' => 'programado'
                ]);
            }
        }
    }

    private function seedProgram6($location)
    {
        $program = Program::firstOrCreate(['nombre' => '6. Preparación ante Emergencias'], ['anio' => 2026, 'estado' => 'aprobado']);
        
        $simulacros = [
            '6.1 Simulacro de Primeros Auxilios',
            '6.2 Simulacro de Accidente de Trabajo',
            '6.3 Simulacro de Sismo',
            '6.4 Simulacro de Amago de Incendio'
        ];

        foreach ($simulacros as $sim) {
            $this->createRecurringActivity($program, $location, $sim, 'Anual', 'JEFE QHSE', Drill::class, [
                'nombre' => $sim,
                'fecha_programada' => '2026-10-01',
                'estado' => 'programado'
            ]);
        }
    }

    private function seedProgram7($location)
    {
        $program = Program::firstOrCreate(['nombre' => '7. Gestión de Incidentes'], ['anio' => 2026, 'estado' => 'aprobado']);

        $this->createRecurringActivity($program, $location, '7.1 Investigación de Incidentes', 'Cuando ocurra', 'JEFE QHSE', Incident::class, [
            'titulo' => 'Incidente Tipo',
            'fecha_ocurrencia' => '2026-01-01 10:00:00',
            'severidad' => 'leve'
        ]);

        $this->createRecurringActivity($program, $location, '7.2 Estadísticas de Accidentabilidad', 'Mensual', 'JEFE QHSE', Documentation::class, [
            'tipo_documento' => 'Reporte Estadístico',
            'titulo' => 'Reporte Mensual de Estadísticas',
            'estado' => 'aprobado'
        ]);
    }

    private function seedProgram8($location)
    {
        $program = Program::firstOrCreate(['nombre' => '8. Promoción y Motivación'], ['anio' => 2026, 'estado' => 'aprobado']);

        $this->createRecurringActivity($program, $location, '8.1 Premiación al Personal', 'Anual', 'JEFE QHSE', Promotion::class, [
            'nombre_campana' => 'Premiación Anual QHSE',
            'fecha_inicio' => '2026-12-01',
            'estado' => 'planificado'
        ]);

        $this->createRecurringActivity($program, $location, '8.2 Reconocimiento al Desempeño', 'Anual', 'JEFE QHSE', Promotion::class, [
            'nombre_campana' => 'Reconocimiento al Desempeño',
            'fecha_inicio' => '2026-12-01',
            'estado' => 'planificado'
        ]);
    }
}
