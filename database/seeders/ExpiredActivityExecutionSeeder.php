<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivityExecution;
use App\Enums\ActivityState;
use Carbon\Carbon;

class ExpiredActivityExecutionSeeder extends Seeder
{
    public function run(): void
    {
        // Get different types of activities
        $types = ['inspeccion', 'capacitacion', 'simulacro', 'auditoria'];
        
        // Ensure we have at least one activity of each type, or just pick random existing ones
        $activities = Activity::whereIn('tipo', $types)->get();

        if ($activities->isEmpty()) {
            // Try getting any activity if specific types aren't found
            $activities = Activity::all();
        }

        if ($activities->isEmpty()) {
            $this->command->info('No activities found to attach executions to. Please seed activities first.');
            return;
        }

        $this->command->info('Creating 5 expired activity executions...');

        // We need 5 examples.
        $count = 0;
        $max = 5;

        // Create 5 executions
        while ($count < $max) {
             // Pick a random activity
             $activity = $activities->random();
             
             // Random date in the past (between 1 and 30 days ago)
             $daysAgo = rand(1, 30);
             $date = Carbon::now()->subDays($daysAgo);
             
             ActivityExecution::create([
                'activity_id' => $activity->id,
                'fecha_programada' => $date,
                'estado' => ActivityState::PROGRAMADO, // Expired means scheduled but not done
                'observacion' => 'Ejecución de prueba vencida generada por seeder.',
            ]);
            
            $this->command->info("Created expired execution for activity: {$activity->nombre} ({$activity->tipo}) on {$date->format('Y-m-d')}");
            $count++;
        }
    }
}
