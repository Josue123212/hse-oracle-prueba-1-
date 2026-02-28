<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear Programa
        $program = \App\Models\Program::updateOrCreate(
            ['nombre' => 'Liderazgo y Compromiso'],
            [
                'descripcion' => 'Programa de actividades correspondientes a la sección 1: Liderazgo y Compromiso del sistema HSE.',
                'anio' => 2026,
                'estado' => 'aprobado',
            ]
        );

        // Actualizar el nombre del programa anterior si existe (opcional, para evitar duplicados si se corrió antes)
        \App\Models\Program::where('nombre', 'Plan Anual QHSE 2026')->delete();

        // 2. Crear Roles y Usuarios (si no existen)
        $roles = [
            'JEFE QHSE' => 'jefe_qhse@hse.com',
            'Médico Ocupacional' => 'medico_ocupacional@hse.com',
            'Supervisor QHSE' => 'supervisor_qhse@hse.com',
            'Asistente QHSE' => 'asistente_qhse@hse.com',
            'Asistente General' => 'asistente_general@hse.com',
        ];

        foreach ($roles as $roleName => $email) {
            $role = \App\Models\Role::firstOrCreate(['name' => $roleName]);
            
            $user = \App\Models\User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $roleName,
                    'password' => bcrypt('password'),
                    'role_id' => $role->id,
                ]
            );
        }

        // 3. Crear Actividades
        $activitiesData = [
            [
                'nombre' => '1.1 Elaboración y Aprobación del Plan Anual QHSE 2026',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'JEFE QHSE',
                'apoyo' => 'SUPERVISOR BASE',
            ],
            [
                'nombre' => '1.2 Elaboración y Aprobación del Programa Anual QHSE 2026',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'JEFE QHSE',
                'apoyo' => 'SUPERVISOR QHSE',
            ],
            [
                'nombre' => '1.3 Definición de los Objetivos y Metas de QHSE Cymtel SAC 2026',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'JEFE QHSE',
                'apoyo' => 'SUPERVISOR QHSE',
            ],
            [
                'nombre' => '1.4 Definición del Alcance de QHSE ECYTEL SAC 2026',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'JEFE QHSE',
                'apoyo' => 'SUPERVISOR QHSE',
            ],
            [
                'nombre' => '1.5 Difusión de la Política en QHSE de ECYTEL SAC y Clientes',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'JEFE QHSE',
                'apoyo' => 'SUPERVISOR QHSE',
            ],
            [
                'nombre' => '1.6 Actualizar la Matriz IPERC',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'JEFE QHSE',
                'apoyo' => 'SUPERVISOR QHSE',
            ],
            [
                'nombre' => '1.7 Actualizar la Matriz IAAS',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'JEFE QHSE',
                'apoyo' => 'SUPERVISOR QHSE',
            ],
            [
                'nombre' => '1.8 Actualizar la Matriz de Monitoreo de Requisitos Legales QHSE ECYTEL SAC 2026',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'JEFE QHSE',
                'apoyo' => 'Asistente QHSE',
            ],
            [
                'nombre' => '1.9 Elaboración y Actualización de Mapas de Riesgos y Rutas de Evacuación',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'Asistente QHSE',
                'apoyo' => 'Asistente QHSE',
            ],
            [
                'nombre' => '1.10 Elaboración de Plan Anual de Salud Ocupacional ECYTEL SAC 2026',
                'responsable' => 'Médico Ocupacional',
                'delegado' => 'Médico Ocupacional',
                'apoyo' => 'Área QHSE',
            ],
            [
                'nombre' => '1.11 Elaboración de Programa Anual de Salud Ocupacional Cymtel SAC 2026',
                'responsable' => 'Médico Ocupacional',
                'delegado' => 'Médico Ocupacional',
                'apoyo' => 'Área QHSE',
            ],
            [
                'nombre' => '1.12 Elaboración de Plan Anual de Manejo Ambiental ECYTEL SAC 2026',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'JEFE QHSE',
                'apoyo' => 'Asistente QHSE',
            ],
            [
                'nombre' => '1.13 Elaboración de Plan Anual de Control y Seguimiento Vehicular Cymtel SAC 2026',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'Asistente General',
                'apoyo' => 'Asistente QHSE',
            ],
            [
                'nombre' => '1.14 Elaboración del Plan de Contingencias ECYTEL SAC 2026',
                'responsable' => 'JEFE QHSE',
                'delegado' => 'JEFE QHSE',
                'apoyo' => 'Asistente QHSE',
            ],
        ];

        foreach ($activitiesData as $data) {
            // Find User ID for Responsable
            $responsableUser = \App\Models\User::where('name', $data['responsable'])->first();

            \App\Models\Activity::updateOrCreate(
                [
                    'program_id' => $program->id,
                    'nombre' => $data['nombre'],
                ],
                [
                    'descripcion' => "Responsable Delegado: {$data['delegado']} | Apoyo: {$data['apoyo']} | Sede: Arequipa Base | Frecuencia: Anual",
                    'frecuencia' => 'anual',
                    'meta' => 100,
                    'unidad_medida' => '%',
                    'es_obligatoria' => true,
                    'responsable_id' => $responsableUser ? $responsableUser->id : null,
                    // Lógica para Enero: Ejecutado, Fechas: 02/01/2026 - 31/01/2026
                    'estado' => 'ejecutado',
                    'fecha_inicio' => '2026-01-02',
                    'fecha_fin' => '2026-01-31',
                ]
            );
        }
    }
}
