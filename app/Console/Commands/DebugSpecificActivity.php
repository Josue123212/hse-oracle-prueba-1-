<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;

class DebugSpecificActivity extends Command
{
    protected $signature = 'debug:activity {id}';
    protected $description = 'Debug a specific activity';

    public function handle()
    {
        $id = $this->argument('id');
        $activity = Activity::find($id);

        if (!$activity) {
            $this->error("Activity not found");
            return;
        }

        $this->info("Activity: " . $activity->nombre);
        $this->info("ID: " . $activity->id);
        $this->info("Frecuencia: " . $activity->frecuencia);
        $this->info("Fecha Inicio (DB): " . $activity->fecha_inicio);
        $this->info("Estado: " . $activity->estado);
        $this->info("Tipo: " . $activity->tipo);
        
        $today = now();
        $this->info("Today is: " . $today->format('Y-m-d'));
        
        // Test Anual Logic
        $dayMatch = $activity->fecha_inicio->day == $today->day ? 'Yes' : 'No (' . $activity->fecha_inicio->day . ' vs ' . $today->day . ')';
        $monthMatch = $activity->fecha_inicio->month == $today->month ? 'Yes' : 'No (' . $activity->fecha_inicio->month . ' vs ' . $today->month . ')';
        
        $this->info("Anual Logic Check:");
        $this->info("Day Match: " . $dayMatch);
        $this->info("Month Match: " . $monthMatch);
    }
}
