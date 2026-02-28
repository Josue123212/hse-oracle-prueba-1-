<?php

namespace App\Console\Commands;

use App\Enums\ActivityState;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateActivityStates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-activity-states';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update activity states based on date and repetition logic (No Cumplio, En Proceso, Ejecutado)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $this->info("Running activity state updates for date: {$today->toDateString()}");

        $activities = Activity::all();
        $countUpdated = 0;

        foreach ($activities as $activity) {
            $originalState = $activity->estado;
            $newState = $originalState;

            // 1. Eventual Logic: Always Ejecutado
            if ($activity->frecuencia === 'eventual') {
                $newState = ActivityState::EJECUTADO;
            }
            // 2. Non-Eventual Logic
            else {
                $veces = (int) ($activity->veces_al_anio ?? 1);
                $ejecuciones = (int) ($activity->ejecuciones_realizadas ?? 0);

                // Check completion first
                if ($ejecuciones >= $veces) {
                    $newState = ActivityState::EJECUTADO;
                } else {
                    // Check next execution date
                    $nextDate = $activity->next_execution_date;

                    if ($nextDate) {
                        // If strictly past (not today), mark as No Cumplio
                        if ($nextDate->lt($today)) {
                            $newState = ActivityState::NO_CUMPLIO;
                        } 
                        // If Today or Future
                        else {
                            // If started but not finished -> En Proceso
                            if ($ejecuciones > 0) {
                                $newState = ActivityState::EN_PROCESO;
                            } 
                            // Not started -> Programado
                            else {
                                $newState = ActivityState::PROGRAMADO;
                            }
                        }
                    } else {
                        // If no next date and not completed -> No Cumplio
                        $newState = ActivityState::NO_CUMPLIO;
                    }
                }
            }

            // Apply update if state changed
            if ($newState !== $originalState) {
                $activity->update(['estado' => $newState]);
                $countUpdated++;
            }
        }

        $this->info("Updated {$countUpdated} activities.");
        return Command::SUCCESS;
    }
}
