<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Session;
use App\Models\ActivityExecution;
use App\Models\Activity;

class ProgramScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (Session::has('hse_program_id')) {
            $programId = Session::get('hse_program_id');
            
            if ($model instanceof ActivityExecution) {
                // ActivityExecution -> Activity -> ProgramComponent -> Program
                $builder->whereHas('activity.component', function (Builder $query) use ($programId) {
                    $query->where('program_id', $programId);
                });
            } elseif ($model instanceof Activity) {
                // Activity -> ProgramComponent -> Program
                $builder->whereHas('component', function (Builder $query) use ($programId) {
                    $query->where('program_id', $programId);
                });
            } else {
                // For all Satellite models (Inspection, Audit, etc.)
                // They all have program_id directly
                $builder->where($model->getTable() . '.program_id', $programId);
            }
        }
    }
}
