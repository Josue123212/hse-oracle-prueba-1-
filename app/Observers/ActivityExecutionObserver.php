<?php

namespace App\Observers;

use App\Models\ActivityExecution;
use App\Models\Activity;

class ActivityExecutionObserver
{
    /**
     * Handle the ActivityExecution "created" event.
     */
    public function created(ActivityExecution $execution): void
    {
        if ($execution->activity_id) {
            $activity = Activity::find($execution->activity_id);
            if ($activity) {
                $activity->increment('ejecuciones_realizadas');
            }
        }
    }

    /**
     * Handle the ActivityExecution "deleted" event.
     */
    public function deleted(ActivityExecution $execution): void
    {
        if ($execution->activity_id) {
            $activity = Activity::find($execution->activity_id);
            if ($activity && $activity->ejecuciones_realizadas > 0) {
                $activity->decrement('ejecuciones_realizadas');
            }
        }
    }
}
