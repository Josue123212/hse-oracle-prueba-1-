<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ActivityState;

class ActivityExecution extends Model
{
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
}
