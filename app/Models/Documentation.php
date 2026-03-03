<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\DeletesLinkedActivity;
use App\Traits\FilteredByProgram;

class Documentation extends Model
{
    use DeletesLinkedActivity, FilteredByProgram;

    protected $guarded = [];

    protected $casts = [
        'fecha_aprobacion' => 'date',
        'fecha_programada' => 'date',
        'proxima_ejecucion' => 'date',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'responsable_id');
    }
}
