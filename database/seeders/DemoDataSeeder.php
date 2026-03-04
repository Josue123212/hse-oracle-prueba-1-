<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\ProgramComponent;
use App\Models\Activity;
use App\Models\ActivityExecution;
use App\Models\Position;
use App\Models\Location;
use App\Enums\ActivityState;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Ensure we have a PositionType, Position and Location
            $posType = \App\Models\PositionType::firstOrCreate(
                ['nombre' => 'Administrativo'],
                ['descripcion' => 'Personal administrativo']
            );

            $position = Position::firstOrCreate(
                ['nombre' => 'Supervisor HSE'],
                [
                    'descripcion' => 'Supervisor de Seguridad',
                    'position_type_id' => $posType->id
                ]
            );

            $location = Location::firstOrCreate(
                ['nombre' => 'Planta Principal'],
                [
                    'direccion' => 'Zona Industrial',
                    'activo' => true
                ]
            );

            // Activity Types
            $activityTypes = [
                'Inspección',
                'Capacitación',
                'Simulacro',
                'Auditoría',
                'Reunión'
            ];

            // 1. Create 2 Programs
            $programsData = [
                [
                    'nombre' => 'Programa de Seguridad Industrial 2026',
                    'descripcion' => 'Gestión de riesgos y prevención de accidentes',
                    'objetivo_general' => 'Reducir la accidentabilidad en un 20%',
                    'anio' => 2026,
                    'estado' => 'activo',
                ],
                [
                    'nombre' => 'Programa de Salud Ocupacional 2026',
                    'descripcion' => 'Vigilancia de la salud de los trabajadores',
                    'objetivo_general' => 'Mejorar el bienestar laboral',
                    'anio' => 2026,
                    'estado' => 'activo',
                ]
            ];

            foreach ($programsData as $pData) {
                $program = Program::create($pData);

                // 2. Create 1 Component per Program
                $component = ProgramComponent::create([
                    'program_id' => $program->id,
                    'name' => 'Gestión General - ' . $program->nombre,
                    'objetivo' => 'Ejecución de actividades principales',
                    'type' => 'subprograma'
                ]);

                // 3. Create Activities per Component (duplicated: scheduled & eventual)
                foreach ($activityTypes as $type) {
                    // --- A. Scheduled (Monthly) ---
                    $scheduledActivity = Activity::create([
                        'program_component_id' => $component->id,
                        'location_id' => $location->id,
                        'nombre' => "$type Mensual - " . $program->nombre,
                        'descripcion' => "Actividad de $type programada mensualmente",
                        'tipo' => $type,
                        'frecuencia' => 'mensual',
                        'veces_al_anio' => 12,
                        'fecha_inicio' => Carbon::create(2026, 1, 1),
                        'fecha_fin' => Carbon::create(2026, 12, 31),
                        'responsable_id' => $position->id,
                        'es_obligatoria' => true,
                        'meta' => 100,
                        'unidad_medida' => '%',
                        'proxima_ejecucion' => Carbon::create(2026, 1, 15),
                    ]);

                    // Generate Monthly Executions
                    for ($m = 1; $m <= 12; $m++) {
                        $date = Carbon::create(2026, $m, 15);
                        $state = $date->isPast() ? ActivityState::EJECUTADO : ActivityState::PROGRAMADO;
                        
                        ActivityExecution::create([
                            'activity_id' => $scheduledActivity->id,
                            'fecha_programada' => $date,
                            'fecha_ejecucion_real' => $date->isPast() ? $date : null,
                            'estado' => $state,
                            'observacion' => $date->isPast() ? 'Ejecución completada según cronograma' : null,
                        ]);
                    }

                    // --- B. Eventual ---
                    $eventualActivity = Activity::create([
                        'program_component_id' => $component->id,
                        'location_id' => $location->id,
                        'nombre' => "$type Eventual - " . $program->nombre,
                        'descripcion' => "Actividad de $type realizada según necesidad",
                        'tipo' => $type,
                        'frecuencia' => 'eventual',
                        'veces_al_anio' => 0, // Indefinite
                        'fecha_inicio' => Carbon::create(2026, 1, 1),
                        'fecha_fin' => Carbon::create(2026, 12, 31),
                        'responsable_id' => $position->id,
                        'es_obligatoria' => false,
                        'meta' => 100,
                        'unidad_medida' => '%',
                    ]);

                    // Generate Random Eventual Executions (e.g., 2)
                    for ($i = 0; $i < 2; $i++) {
                        $date = Carbon::create(2026, rand(1, 6), rand(1, 28)); // Random date in first half of year
                        ActivityExecution::create([
                            'activity_id' => $eventualActivity->id,
                            'fecha_programada' => $date, // Usually eventuals might not have scheduled date, but keeping structure
                            'fecha_ejecucion_real' => $date,
                            'estado' => ActivityState::EJECUTADO,
                            'observacion' => 'Ejecución extraordinaria solicitada',
                        ]);
                    }
                }
            }
        });
    }
}
