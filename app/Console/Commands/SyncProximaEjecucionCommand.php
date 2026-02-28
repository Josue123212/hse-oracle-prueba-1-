<?php

namespace App\Console\Commands;

use App\Models\Activity;
use Illuminate\Console\Command;

class SyncProximaEjecucionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:sync-proxima-ejecucion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates and syncs proxima_ejecucion for all activities and their children.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync of proxima_ejecucion...');

        $activities = Activity::all();
        $count = $activities->count();
        $bar = $this->output->createProgressBar($count);

        foreach ($activities as $activity) {
            // Trigger saving observer to calculate proxima_ejecucion
            // Trigger updated observer to sync to child
            // We use save() which does both if the model is dirty or if we force it.
            // However, if nothing changed, save() might not fire events.
            // We can force an update by touching timestamp or just setting proxima_ejecucion explicitly first.
            
            // Let's force calculation here to be sure, or rely on observer.
            // Observer logic:
            // $activity->proxima_ejecucion = ... in saving()
            
            // To ensure saving() is called even if nothing else changed, we can touch the model.
            $activity->touch(); 
            // touch() saves the model, triggering saving and updated events.
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Sync completed successfully.');
    }
}
