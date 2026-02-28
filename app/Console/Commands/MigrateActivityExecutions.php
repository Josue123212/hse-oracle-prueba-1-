<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\ActivityExecution;
use Illuminate\Console\Command;
use Carbon\Carbon;

class MigrateActivityExecutions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-activity-executions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate activity executions based on frequency and start date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $activities = Activity::all();
        $this->info('Processing ' . $activities->count() . ' activities...');

        foreach ($activities as $activity) {
            // Clear existing executions to avoid duplicates during dev
            $activity->executions()->delete();

            if (!$activity->fecha_inicio) {
                $this->warn("Activity {$activity->id} ({$activity->nombre}) has no start date. Skipping.");
                continue;
            }
            
            if ($activity->frecuencia === 'eventual') {
                 ActivityExecution::create([
                    'activity_id' => $activity->id,
                    'fecha_programada' => $activity->fecha_inicio,
                    'estado' => ($activity->estado === 'ejecutado' || $activity->ejecuciones_realizadas > 0) ? 'ejecutado' : 'pendiente',
                    'fecha_ejecucion_real' => ($activity->estado === 'ejecutado' || $activity->ejecuciones_realizadas > 0) ? $activity->fecha_inicio : null,
                ]);
                continue;
            }

            $dates = [];
            $current = Carbon::parse($activity->fecha_inicio);
            $veces = (int) ($activity->veces_al_anio ?? 1);
            
            // Safety check for excessive records
            if ($veces > 366) {
                $this->warn("Activity {$activity->id} has {$veces} repetitions. Limiting to 366.");
                $veces = 366;
            }

            for ($i = 0; $i < $veces; $i++) {
                $dates[] = $current->copy();
                
                switch ($activity->frecuencia) {
                    case 'diario':
                        $current->addDay();
                        break;
                    case 'semanal':
                        $current->addWeek();
                        break;
                    case 'mensual':
                        $current->addMonth();
                        break;
                    case 'trimestral':
                        $current->addMonths(3);
                        break;
                    case 'semestral':
                        $current->addMonths(6);
                        break;
                    case 'anual':
                        $current->addYear();
                        break;
                    default:
                        $current->addMonth();
                }
            }

            $executedCount = $activity->ejecuciones_realizadas ?? 0;
            
            foreach ($dates as $index => $date) {
                $status = 'pendiente';
                $realDate = null;

                if ($index < $executedCount) {
                    $status = 'ejecutado';
                    $realDate = $date; // Assumption
                } else {
                    if ($date->lt(now()->startOfDay())) {
                        $status = 'no_cumplio';
                    }
                }
                
                ActivityExecution::create([
                    'activity_id' => $activity->id,
                    'fecha_programada' => $date->format('Y-m-d'),
                    'estado' => $status,
                    'fecha_ejecucion_real' => $realDate,
                ]);
            }
        }
        
        $this->info('Activity executions generated successfully.');
    }
}
