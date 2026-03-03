<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ActivityState;
use App\Traits\FilteredByProgram;

class ActivityExecution extends Model
{
    use SoftDeletes, FilteredByProgram;

    protected $fillable = [
        'activity_id',
        'fecha_programada',
        'fecha_ejecucion_real',
        'estado',
        'observacion',
        'evidencia',
        'data',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_ejecucion_real' => 'date',
        'data' => 'array',
        'evidencia' => 'array',
        'estado' => ActivityState::class,
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(ExecutionEvidence::class, 'execution_id');
    }
}
