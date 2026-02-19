<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repository extends Model
{
    protected $fillable = [
        'nombre',
        'url',
        'estado',
        'fecha_creacion',
        'repositoriable_type',
        'repositoriable_id',
        'esta_llena',
        'ultima_verificacion',
        'user_id',
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'ultima_verificacion' => 'datetime',
        'esta_llena' => 'boolean',
    ];

    public function repositoriable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

