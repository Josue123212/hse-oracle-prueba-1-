<?php

namespace App\Models;

use App\Enums\ActivityState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\DeletesLinkedActivity;
use App\Traits\FilteredByProgram;

class Audit extends Model
{
    use DeletesLinkedActivity, FilteredByProgram;

    protected $fillable = [
        'program_id',
        'activity_id',
        'nombre',
        'descripcion',
        'fecha_programada',
        'fecha_vencimiento',
        'hallazgos',
        'auditor_id',
        'tipo_auditoria',
        'entidad_auditora',
        'alcance',
        'auditores',
        'frecuencia',
        'veces_al_anio',
        'ejecuciones_realizadas',
        'detalle_frecuencia',
        'proxima_ejecucion',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_ejecucion' => 'date',
        'proxima_ejecucion' => 'date',
    ];

    protected static function booted()
    {
        /*
        static::saved(function ($model) {
            if ($model->activity) {
                $updates = [];
                if ($model->isDirty('fecha_programada')) $updates['fecha_inicio'] = $model->fecha_programada;
                if ($model->isDirty(['frecuencia', 'veces_al_anio', 'ejecuciones_realizadas', 'detalle_frecuencia'])) {
                    $updates['frecuencia'] = $model->frecuencia;
                    $updates['veces_al_anio'] = $model->veces_al_anio;
                    $updates['ejecuciones_realizadas'] = $model->ejecuciones_realizadas;
                    $updates['detalle_frecuencia'] = $model->detalle_frecuencia;
                }
                if (!empty($updates)) $model->activity->update($updates);
            }
        });
        */
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'auditor_id');
    }
}
