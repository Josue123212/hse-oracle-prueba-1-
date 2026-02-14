<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'report_type_id',
        'user_id',
        'fecha',
        'fecha_subida',
        'estado',
        'archivo_detectado',
        'observaciones',
    ];

    protected $casts = [
        'archivo_detectado' => 'boolean',
        'fecha' => 'date',
        'fecha_subida' => 'datetime',
    ];

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
