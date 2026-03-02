<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityExecution;
use App\Enums\ActivityState;

class UpdateActivityStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-activity-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates activity status to NO_CUMPLIO if deadline passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();

        $count = ActivityExecution::query()
            ->where('fecha_programada', '<', $today)
            ->whereIn('estado', [
                ActivityState::PROGRAMADO,
                ActivityState::EN_PROCESO
            ])
            ->update(['estado' => ActivityState::NO_CUMPLIO]);

        $this->info("Updated {$count} expired activities to NO_CUMPLIO.");
    }
}
