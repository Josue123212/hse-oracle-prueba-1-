<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingAttendance extends Model
{
    protected $fillable = [
        'training_id',
        'user_id',
        'fecha_inicio',
        'fecha_fin',
        'firma_digital',
        'estado',
        'aprobado',
        'certificado_url',
    ];

    protected $casts = [
        'aprobado' => 'boolean',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
