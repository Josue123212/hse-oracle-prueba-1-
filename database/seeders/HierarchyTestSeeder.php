<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Program;
use App\Models\ProgramComponent;
use App\Models\Activity;
use App\Models\ActivityExecution;
use App\Services\ActivityService;
use Carbon\Carbon;

// Importar modelos satélite para limpieza
use App\Models\Inspection;
use App\Models\Audit;
use App\Models\Training;
use App\Models\Drill;
use App\Models\Incident;
use App\Models\Committee;
use App\Models\Documentation;
use App\Models\Promotion;
use App\Models\OperationalControl;

class HierarchyTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = new ActivityService();

        // 1. Limpiar datos existentes de TODAS las tablas relacionadas
        // Orden importante para evitar restricciones de clave foránea
        ActivityExecution::query()->delete();
        
        // Limpiar tablas satélite
        Inspection::query()->delete();
        Audit::query()->delete();
        Training::query()->delete();
        Drill::query()->delete();
        Incident::query()->delete();
        Committee::query()->delete();
        Documentation::query()->delete();
        Promotion::query()->delete();
        OperationalControl::query()->delete();

        // Limpiar tablas principales
        Activity::query()->delete();
        ProgramComponent::query()->delete();
        Program::query()->delete();

        $this->command->info('Tablas limpiadas correctamente.');

        $activityTypes = [
            'general',
            'auditoria',
            'inspeccion',
            'capacitacion',
            'simulacro',
            'incidente',
            'documentacion',
            'promocion',
            'control_operacional',
            'comite'
        ];

        // 2. Crear Programas y Componentes
        for ($i = 1; $i <= 2; $i++) {
            $program = Program::create([
                'nombre' => "Programa de Prueba {$i}",
                'descripcion' => "Descripción del programa {$i} generado por seeder",
                'objetivo_general' => "Objetivo General del Programa {$i}: Mejorar la gestión HSE integral.",
                'anio' => 2026,
                'estado' => 'borrador',
            ]);

            // Crear 1 Componente por Programa
            $component = ProgramComponent::create([
                'program_id' => $program->id,
                'name' => "Componente {$i} (Operativo)",
                'type' => 'subprograma',
                'objetivo' => "Objetivo Específico del Componente {$i}: Implementar controles operativos.",
            ]);

            $this->command->info("Creado Programa: {$program->nombre} con Componente: {$component->name}");

            // 3. Crear actividades de cada tipo usando el SERVICIO
            foreach ($activityTypes as $type) {
                try {
                    $data = [
                        'program_component_id' => $component->id,
                        'nombre' => "Actividad {$type} - Prog {$i}",
                        'descripcion' => "Descripción generada para actividad tipo {$type}",
                        'frecuencia' => 'mensual',
                        'veces_al_anio' => 12,
                        'meta' => rand(80, 100),
                        'es_obligatoria' => true,
                        'unidad_medida' => 'Porcentaje',
                        'fecha_inicio' => Carbon::now()->startOfYear(), // Enero 1
                        'fecha_fin' => Carbon::now()->endOfYear(),
                        'execution_period' => 'mensual',
                        'program_id_selector' => $program->id, // Para compatibilidad si el servicio lo usa, aunque ya lo arreglamos
                        
                        // Campos específicos para satélites (opcionales pero buenos para testear)
                        'lugar' => "Sede Principal - Area {$i}", // Inspecciones, Incidentes
                        'tema' => "Tema de capacitación {$i}", // Capacitaciones
                        'observaciones' => 'Observación inicial de prueba',
                    ];

                    // Usar el servicio para crear la actividad y su satélite
                    $activity = $service->createWithType($data, $type);
                    
                    $this->command->info("  -> Creada Actividad: {$activity->nombre} (Tipo: {$type}) [ID: {$activity->id}]");

                } catch (\Exception $e) {
                    $this->command->error("  !! Error creando actividad tipo {$type}: " . $e->getMessage());
                }
            }
        }
        
        $this->command->info('HierarchyTestSeeder completado exitosamente.');
    }
}
