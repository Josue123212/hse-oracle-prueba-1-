<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Program;
use App\Models\ProgramComponent;
use App\Models\Location;
use App\Models\Position;
use App\Models\PositionType;
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
use Illuminate\Support\Facades\File;

class PlanQHSE2026Seeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando carga del Plan QHSE 2026 desde MD...');

        // 0. Setup dependencies
        $defaultPositionType = PositionType::firstOrCreate(
            ['nombre' => 'Administrativo']
        );

        $program = Program::firstOrCreate(
            ['nombre' => 'PROGRAMA ANUAL QHSE'],
            [
                'descripcion' => 'Programa Anual de Gestión de Seguridad, Salud y Medio Ambiente 2026',
                'anio' => 2026,
                'estado' => 'aprobado',
                'objetivo_general' => 'Garantizar un ambiente de trabajo seguro y saludable para todos los colaboradores.',
            ]
        );

        // 1. Read MD File
        $mdPath = base_path('planteamiento/rellenado_de_datos.md');
        if (!File::exists($mdPath)) {
            $this->command->error("Archivo MD no encontrado: $mdPath");
            return;
        }

        $lines = explode("\n", File::get($mdPath));
        $activitiesMap = [];
        $rows = [];

        // 2. Parse MD
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Definition: "1.1 = Name"
            if (preg_match('/^([A-Z0-9\.]+)\s*=\s*(.*)$/', $line, $matches) && !str_contains($line, '//')) {
                $activitiesMap[$matches[1]] = trim($matches[2]);
            }

            // Data Row: "Comp//Code//Details//Dates"
            if (str_contains($line, '//')) {
                $mainParts = explode('//', $line);
                if (count($mainParts) >= 3) {
                    $compKey = trim($mainParts[0]);
                    $code = trim($mainParts[1]);
                    $detailsStr = trim($mainParts[2]);
                    $datesStr = isset($mainParts[3]) ? trim($mainParts[3]) : '';

                    $details = explode('/', $detailsStr);
                    
                    // Handle cases where '/' might be used inside a field?
                    // Assuming strict format for now based on observation.
                    // We expect at least 7 fields: Meta, Sede, Resp, Del, Apoyo, Freq, Tipo
                    if (count($details) >= 7) {
                        $rows[] = [
                            'compKey' => $compKey,
                            'code' => $code,
                            'meta' => $details[0],
                            'sedeName' => $details[1],
                            'respName' => $details[2],
                            'delName' => $details[3],
                            'apoyo' => $details[4],
                            'freqStr' => $details[5],
                            'tipo' => $details[6],
                            'datesStr' => $datesStr
                        ];
                    } else {
                         $this->command->warn("Skipping line (invalid details format): $line");
                    }
                }
            }
        }
        
        $this->command->info("Found " . count($rows) . " rows to process.");
        
        // Component Maps (hardcoded names for components/subcomponents)
        $componentsMap = [
            '1' => 'Liderazgo y Compromiso',
            '2' => 'Evaluación y Seguimiento',
            '3' => 'Inspecciones Planeadas',
            '4' => 'Análisis de Riesgos y Trabajo Seguro',
            '5' => 'Capacitación y Entrenamiento',
            '6' => 'Preparación ante Emergencias',
            '7' => 'Gestión de Incidentes, Accidentes y Enfermedades Ocupacionales',
            '8' => 'Promoción y Motivación',
        ];

        $subComponentsMap = [
            '5/A' => 'Entrenamiento',
            '5/B' => 'Capacitaciones en Seguridad',
            '5/C' => 'Capacitaciones en Salud Ocupacional',
            '5/D' => 'Capacitaciones en Medio Ambiente',
            '5/E' => 'Capacitaciones al Comité de Seguridad y Salud en el Trabajo',
            '5/F' => 'Capacitaciones Específicas',
        ];

        $locationsCache = [];
        $positionsCache = [];
        $componentsCache = [];

        $adminUser = \App\Models\User::first();

        // 3. Process Rows
        foreach ($rows as $index => $row) {
            try {
                $this->command->info("Processing row " . ($index + 1) . " of " . count($rows) . ": " . $row['code']);
                // Unpack row
                $compKey = $row['compKey'];
            $code = $row['code'];
            $meta = $row['meta'];
            $sedeName = $row['sedeName'];
            $respName = $row['respName'];
            $delName = $row['delName'];
            $apoyo = $row['apoyo'];
            $freqStr = $row['freqStr'];
            $tipo = $row['tipo'];
            $datesStr = $row['datesStr'];

            // 3.1 Location
            if (!isset($locationsCache[$sedeName])) {
                $locationsCache[$sedeName] = Location::firstOrCreate(
                    ['nombre' => $sedeName],
                    ['activo' => true, 'direccion' => 'Sede ' . $sedeName]
                )->id;
            }
            $locationId = $locationsCache[$sedeName];

            // 3.2 Positions
            $respName = str_replace('Medico', 'Médico', $respName);
            $delName = str_replace('Medico', 'Médico', $delName);

            if (!isset($positionsCache[$respName])) {
                $positionsCache[$respName] = Position::firstOrCreate(
                    ['nombre' => $respName],
                    ['descripcion' => 'Cargo generado automáticamente', 'position_type_id' => $defaultPositionType->id]
                )->id;
            }
            $respId = $positionsCache[$respName];

            if (!isset($positionsCache[$delName])) {
                $positionsCache[$delName] = Position::firstOrCreate(
                    ['nombre' => $delName],
                    ['descripcion' => 'Cargo generado automáticamente', 'position_type_id' => $defaultPositionType->id]
                )->id;
            }
            $delId = $positionsCache[$delName];

            // 3.3 Components
            $componentId = null;
            if (str_contains($compKey, '/')) {
                // Subcomponent
                [$parentKey, $subKey] = explode('/', $compKey);
                
                // Parent
                if (!isset($componentsCache[$parentKey])) {
                    $componentsCache[$parentKey] = ProgramComponent::firstOrCreate(
                        ['program_id' => $program->id, 'name' => $componentsMap[$parentKey] ?? "Componente $parentKey"],
                        ['type' => 'elemento']
                    )->id;
                }
                $parentId = $componentsCache[$parentKey];

                // Sub
                if (!isset($componentsCache[$compKey])) {
                    $componentsCache[$compKey] = ProgramComponent::firstOrCreate(
                        ['program_id' => $program->id, 'name' => $subComponentsMap[$compKey] ?? "Subcomponente $compKey"],
                        ['type' => 'subprograma', 'parent_id' => $parentId]
                    )->id;
                }
                $componentId = $componentsCache[$compKey];
            } else {
                // Main Component
                if (!isset($componentsCache[$compKey])) {
                    $componentsCache[$compKey] = ProgramComponent::firstOrCreate(
                        ['program_id' => $program->id, 'name' => $componentsMap[$compKey] ?? "Componente $compKey"],
                        ['type' => 'elemento']
                    )->id;
                }
                $componentId = $componentsCache[$compKey];
            }

            // 3.4 Frequency & Dates
            $freqLower = mb_strtolower($freqStr);
            $frecuencia = 'anual';
            $vecesAlAnio = 1;
            $detalleFrecuencia = null;
            $isEventual = false;

            if (str_contains($freqLower, 'anual')) {
                $frecuencia = 'anual';
                $vecesAlAnio = 1;
            } elseif (str_contains($freqLower, 'mensual')) {
                $frecuencia = 'mensual';
                $vecesAlAnio = 12;
            } elseif (str_contains($freqLower, 'trimestral')) {
                $frecuencia = 'trimestral';
                $vecesAlAnio = 4;
            } elseif (str_contains($freqLower, 'diario')) {
                $frecuencia = 'diario';
                $vecesAlAnio = 365;
            } else {
                $frecuencia = 'eventual';
                $detalleFrecuencia = $freqStr;
                $isEventual = true;
                $vecesAlAnio = 1;
            }

            // Start Date Logic
            $fechaInicio = '2026-01-01';
            if ($isEventual) {
                // Eventuales: sin fecha de inicio fija o null
                $fechaInicio = null;
            }

            // 3.5 Activity Name Lookup
            $activityName = $activitiesMap[$code] ?? null;
            if (!$activityName && str_starts_with($compKey, '5/')) {
                 $activityName = $activitiesMap["5.$code"] ?? null;
            }
            if (!$activityName) {
                $activityName = "Actividad $code";
            }

            // 3.6 Create Activity
            $activity = Activity::create([
                'program_component_id' => $componentId,
                'location_id' => $locationId,
                'nombre' => $activityName,
                'descripcion' => "Código: $code. Importado del Plan QHSE 2026.",
                'tipo' => $tipo,
                'frecuencia' => $frecuencia,
                'detalle_frecuencia' => $detalleFrecuencia,
                'veces_al_anio' => $vecesAlAnio,
                'meta' => (int) str_replace('%', '', $meta),
                'unidad_medida' => str_contains($meta, '%') ? '%' : 'Unidad',
                'es_obligatoria' => true,
                'responsable_id' => $respId,
                'responsable_delegado_id' => $delId,
                'apoyo' => $apoyo,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => '2026-12-31',
                'proxima_ejecucion' => null,
            ]);

            // 3.7 Create Executions
            if ($frecuencia === 'diario') {
                $startDate = \Carbon\Carbon::create(2026, 1, 1);
                $endDate = \Carbon\Carbon::create(2026, 12, 31);
                
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    ActivityExecution::create([
                        'activity_id' => $activity->id,
                        'fecha_programada' => $date->format('Y-m-d'),
                        'estado' => ActivityState::PROGRAMADO->value,
                        'observacion' => 'Generado automáticamente (Diario)',
                    ]);
                }
            } elseif (!empty($datesStr)) {
                $dates = explode(',', $datesStr);
                foreach ($dates as $date) {
                    $date = trim($date);
                    if (empty($date)) continue;
                    
                    ActivityExecution::create([
                        'activity_id' => $activity->id,
                        'fecha_programada' => $date,
                        'estado' => ActivityState::PROGRAMADO->value,
                        'observacion' => 'Generado automáticamente',
                    ]);
                }
            }

            // 3.8 Create Specific Activity Record
            $firstDate = $fechaInicio;
            if (empty($firstDate) && !empty($datesStr)) {
                $datesArray = explode(',', $datesStr);
                if (count($datesArray) > 0) {
                    $firstDate = trim($datesArray[0]);
                }
            }
            if (empty($firstDate)) {
                $firstDate = '2026-01-01';
            }

            $baseData = [
                'program_id' => $program->id,
                'activity_id' => $activity->id,
                'fecha_programada' => $firstDate,
                'responsable_id' => $respId,
                'frecuencia' => $frecuencia,
                'veces_al_anio' => $vecesAlAnio,
                'detalle_frecuencia' => $detalleFrecuencia,
                'proxima_ejecucion' => null,
            ];

            switch ($tipo) {
                case 'inspeccion':
                    Inspection::create(array_merge($baseData, [
                        'nombre' => $activity->nombre,
                        'descripcion' => $activity->descripcion,
                        'lugar' => $sedeName,
                        'tipo_inspeccion' => 'General',
                        'location_id' => $locationId,
                    ]));
                    break;
                case 'auditoria':
                    Audit::create(array_merge($baseData, [
                        'nombre' => $activity->nombre,
                        'descripcion' => $activity->descripcion,
                        'tipo_auditoria' => 'Interna',
                        'entidad_auditora' => 'Interna',
                        'alcance' => 'General',
                        'auditores' => 'Equipo Auditor',
                        'responsable_id' => $adminUser ? $adminUser->id : null,
                    ]));
                    break;
                case 'capacitacion':
                    Training::create(array_merge($baseData, [
                        'tema' => $activity->nombre,
                        'descripcion' => $activity->descripcion,
                        'duracion_horas' => 1,
                        'asistentes_esperados' => 10,
                    ]));
                    break;
                case 'simulacro':
                    Drill::create(array_merge($baseData, [
                        'nombre' => $activity->nombre,
                        'descripcion' => $activity->descripcion,
                        'escenario' => 'Simulacro General',
                    ]));
                    break;
                case 'incidente':
                    Incident::create(array_merge($baseData, [
                        'titulo' => $activity->nombre,
                        'descripcion' => $activity->descripcion,
                        'fecha_ocurrencia' => $firstDate,
                        'lugar' => $sedeName,
                        'severidad' => 'leve',
                    ]));
                    break;
                case 'comite':
                    Committee::create(array_merge($baseData, [
                        'nombre' => $activity->nombre,
                        'tema_principal' => $activity->nombre,
                    ]));
                    break;
                case 'documentacion':
                    Documentation::create(array_merge($baseData, [
                        'titulo' => $activity->nombre,
                        'descripcion' => $activity->descripcion,
                        'tipo_documento' => 'Documento QHSE',
                    ]));
                    break;
                case 'promocion':
                    Promotion::create(array_merge($baseData, [
                        'nombre_campana' => $activity->nombre,
                        'descripcion' => $activity->descripcion,
                        'publico_objetivo' => 'Todos',
                    ]));
                    break;
                case 'control_operacional':
                    OperationalControl::create(array_merge($baseData, [
                        'nombre_proceso' => $activity->nombre,
                        'observaciones' => $activity->descripcion,
                        'parametro' => 'Cumplimiento',
                        'valor_esperado' => '100%',
                    ]));
                    break;
            }
            } catch (\Exception $e) {
                $this->command->error("Error processing row " . ($index + 1) . " (" . $row['code'] . "): " . $e->getMessage());
                $this->command->error($e->getTraceAsString());
            }
        }
        
        $this->command->info('Plan QHSE 2026 cargado exitosamente.');
    }
}
