<?php

namespace Database\Seeders;

use App\Models\Audit;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestAuditsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurarse de tener un programa y un usuario
        $program = Program::first();
        if (!$program) {
            $program = Program::create([
                'nombre' => 'Programa de Prueba',
                'descripcion' => 'Programa para pruebas de auditoría',
                'anio' => 2026,
                'estado' => 'aprobado',
            ]);
        }

        $auditor = User::first(); // Usar el primer usuario como auditor

        // Crear dos auditorías para hoy
        Audit::create([
            'program_id' => $program->id,
            'nombre' => 'Auditoría de Seguridad Física',
            'descripcion' => 'Verificación de controles de acceso y seguridad perimetral.',
            'fecha_programada' => now()->toDateString(), // Hoy
            'estado' => 'programado',
            'auditor_id' => $auditor ? $auditor->id : null,
        ]);

        Audit::create([
            'program_id' => $program->id,
            'nombre' => 'Auditoría de Procesos Internos',
            'descripcion' => 'Revisión de cumplimiento de procedimientos operativos estándar.',
            'fecha_programada' => now()->toDateString(), // Hoy
            'estado' => 'programado',
            'auditor_id' => $auditor ? $auditor->id : null,
        ]);

        $this->command->info('Se han creado 2 auditorías de prueba para hoy.');
    }
}
