<?php

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;

trait DeletesLinkedActivity
{
    protected static function bootDeletesLinkedActivity()
    {
        static::deleted(function (Model $model) {
            if ($model->activity_id) {
                // Find the activity
                $activity = Activity::find($model->activity_id);
                
                // If it exists, delete it
                // This will trigger the cascade delete for this model again if not careful,
                // but since this model is already deleted, it should be fine.
                // We use deleteQuietly() if available to avoid side effects, but delete() is standard.
                if ($activity) {
                    $activity->delete();
                }
            }
        });
    }
}
