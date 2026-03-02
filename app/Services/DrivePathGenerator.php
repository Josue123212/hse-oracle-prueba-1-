<?php

namespace App\Services;

use App\Models\ActivityExecution;
use App\Models\Activity;
use App\Models\Program;

class DrivePathGenerator
{
    public static function generate(ActivityExecution|Activity $record): string
    {
        $path = [];
        
        // 1. Obtener la actividad y su programa base
        $activity = $record instanceof ActivityExecution ? $record->activity : $record;
        
        if ($activity && $activity->program) {
            // 2. Construir jerarquía de programas (recursivo hacia arriba)
            $program = $activity->program;
            $programPath = [];
            
            while ($program) {
                // Añadir al inicio del array para mantener orden Padre -> Hijo
                array_unshift($programPath, self::sanitize($program->nombre));
                $program = $program->parent; // Asumiendo relación 'parent' en modelo Program
            }
            
            $path = array_merge($path, $programPath);
        } else {
            $path[] = 'Sin_Programa';
        }

        // 3. Añadir nombre de la actividad
        $path[] = self::sanitize($activity->nombre ?? 'Actividad_Sin_Nombre');

        // 4. Añadir fecha de ejecución
        if ($record instanceof ActivityExecution) {
            $date = $record->fecha_ejecucion_real 
                ?? $record->fecha_programada 
                ?? now();
        } else {
            $date = now();
        }
                
        $path[] = $date->format('Y-m-d');

        // Resultado: "Programa Padre/Sub Programa/Actividad X/2023-10-27"
        return implode('/', $path);
    }

    private static function sanitize(string $name): string
    {
        // Eliminar caracteres ilegales para rutas y reemplazar espacios
        return preg_replace('/[^A-Za-z0-9_\- ]/', '', $name);
    }
}
