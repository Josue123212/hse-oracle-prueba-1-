<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingMaterial extends Model
{
    protected $fillable = [
        'training_id',
        'nombre',
        'url_material',
        'archivo',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }
}
