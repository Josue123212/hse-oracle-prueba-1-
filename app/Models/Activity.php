<?php

namespace App\Models;

use App\Enums\ActivityState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;
use App\Models\ActivityExecution;

use Illuminate\Database\Eloquent\Relations\HasOne;

class Activity extends Model
{
    protected static function booted(): void
    {
        static::saved(function (Activity $activity) {
            if ($activity->wasChanged(['fecha_inicio', 'frecuencia', 'veces_al_anio', 'fecha_fin'])) {
                $activity->regenerateExecutions();
            }
        });
        
        static::created(function (Activity $activity) {
            $activity->regenerateExecutions();
        });
    }

    protected $fillable = [
        'program_id',
        'location_id',
        'nombre',
        'descripcion',
        'es_plantilla',
        'parent_id',
        'veces_al_anio',
        'ejecuciones_realizadas',
        'execution_period',
        'tipo',
        'frecuencia',
        'detalle_frecuencia',
        'meta',
        'unidad_medida',
        'es_obligatoria',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'proxima_ejecucion',
        'responsable_id',
    ];

    protected $casts = [
        'es_obligatoria' => 'boolean',
        'es_plantilla' => 'boolean',
        'meta' => 'integer',
        'veces_al_anio' => 'integer',
        'ejecuciones_realizadas' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'proxima_ejecucion' => 'date',
        'estado' => ActivityState::class,
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // Self-referencing relationships for Master-Detail pattern
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Activity::class, 'parent_id');
    }

    public function audit(): HasOne
    {
        return $this->hasOne(Audit::class);
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class);
    }

    public function training(): HasOne
    {
        return $this->hasOne(Training::class);
    }

    public function drill(): HasOne
    {
        return $this->hasOne(Drill::class);
    }

    public function incident(): HasOne
    {
        return $this->hasOne(Incident::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(ActivityExecution::class);
    }

    public function committee(): HasOne
    {
        return $this->hasOne(Committee::class);
    }

    /**
     * Calculate the next execution date based on start date, frequency, and executions.
     */
    public function getNextExecutionDateAttribute(): ?\Carbon\Carbon
    {
        if (!$this->fecha_inicio) {
            return null;
        }

        if ($this->frecuencia === 'eventual') {
            return null;
        }

        $veces = (int) ($this->veces_al_anio ?? 1);
        $ejecuciones = (int) ($this->ejecuciones_realizadas ?? 0);

        if ($ejecuciones >= $veces) {
            return null; // Completed
        }

        $next = $this->fecha_inicio->copy();

        // If single execution, the date is just the start date
        if ($veces <= 1) {
            return $next;
        }

        // For multiple executions, advance by the number of completed executions
        for ($i = 0; $i < $ejecuciones; $i++) {
            match($this->frecuencia) {
                'diario' => $next->addDay(),
                'semanal' => $next->addWeek(),
                'mensual' => $next->addMonth(),
                'trimestral' => $next->addMonths(3),
                'semestral' => $next->addMonths(6),
                'anual' => $next->addYear(),
                default => $next->addMonth(), // Default fallback
            };
        }

        return $next;
    }

    public function documentation(): HasOne
    {
        return $this->hasOne(Documentation::class);
    }

    public function promotion(): HasOne
    {
        return $this->hasOne(Promotion::class);
    }

    public function operationalControl(): HasOne
    {
        return $this->hasOne(OperationalControl::class);
    }

    public function regenerateExecutions(): void
    {
        if (!$this->fecha_inicio || !$this->frecuencia) {
            return;
        }

        // Delete future/pending/failed executions
        $this->executions()
             ->whereIn('estado', [
                 ActivityState::PROGRAMADO, 
                 'pendiente', 
                 ActivityState::NO_CUMPLIO, 
                 'vencido'
             ])
             ->delete();

        if ($this->frecuencia === 'eventual') {
            if ($this->executions()->count() === 0) {
                 $this->executions()->create([
                    'fecha_programada' => $this->fecha_inicio,
                    'estado' => ActivityState::PROGRAMADO,
                    'responsable_id' => $this->responsable_id,
                ]);
            }
            return;
        }

        $dates = [];
        $current = Carbon::parse($this->fecha_inicio);
        $veces = (int) ($this->veces_al_anio ?? 1);
        
        if ($veces > 366) $veces = 366;

        for ($i = 0; $i < $veces; $i++) {
            $dates[] = $current->copy();
            
            switch ($this->frecuencia) {
                case 'diario': $current->addDay(); break;
                case 'semanal': $current->addWeek(); break;
                case 'mensual': $current->addMonth(); break;
                case 'trimestral': $current->addMonths(3); break;
                case 'semestral': $current->addMonths(6); break;
                case 'anual': $current->addYear(); break;
                default: $current->addMonth();
            }
        }

        foreach ($dates as $date) {
            $exists = $this->executions()
                           ->whereDate('fecha_programada', $date)
                           ->exists();

            if (!$exists) {
                $this->executions()->create([
                    'fecha_programada' => $date,
                    'estado' => ActivityState::PROGRAMADO,
                    'responsable_id' => $this->responsable_id,
                ]);
            }
        }
    }

    /**
     * Update the activity status based on its progress.
     * Rule:
     * - 0/X -> programado (unless already vencido/etc, but for progress sync we reset)
     * - N/X -> en_proceso (0 < N < X)
     * - X/X -> ejecutado
     */
    public function updateStatusBasedOnProgress(): void
     {
         // Ensure valid numbers
         $total = max(1, (int)$this->veces_al_anio);
         $current = max(0, (int)$this->ejecuciones_realizadas);

         if ($current >= $total) {
             $this->estado = 'ejecutado';
             if (!$this->fecha_fin) {
                 $this->fecha_fin = now();
             }
         } elseif ($current > 0) {
             $this->estado = 'en_proceso';
         } else {
             // 0 progress -> programado (unless vencido)
             if ($this->estado !== 'vencido') {
                 $this->estado = 'programado';
             }
         }
         
         $this->save();
     }

    /**
     * Get the formatted list of scheduled months/dates.
     */
    public function getScheduledMonthsAttribute(): string
    {
        if ($this->frecuencia === 'eventual') {
            return 'Eventual' . ($this->detalle_frecuencia ? ': ' . $this->detalle_frecuencia : '');
        }

        if (!$this->fecha_inicio) {
            return 'N/A';
        }

        if (in_array($this->frecuencia, ['diario', 'semanal'])) {
            return ucfirst($this->frecuencia);
        }

        $dates = [];
        $current = $this->fecha_inicio->copy();
        // Calculate based on veces_al_anio or execution_period
        // Assuming we want to show the cycle for the year starting from start date
        $veces = (int) ($this->veces_al_anio ?? 1);
        
        // For annual, just one month
        if ($this->frecuencia === 'anual') {
            return ucfirst($current->isoFormat('MMM'));
        }

        for ($i = 0; $i < $veces; $i++) {
            $dates[] = ucfirst($current->isoFormat('MMM'));
            
            // Advance current for next iteration
            switch ($this->frecuencia) {
                case 'mensual':
                    $current->addMonth();
                    break;
                case 'trimestral':
                    $current->addMonths(3);
                    break;
                case 'semestral':
                    $current->addMonths(6);
                    break;
                default:
                    $current->addMonth();
            }
        }

        return implode(', ', $dates);
    }

    /**
     * Get the next upcoming execution date relative to today.
     * This ignores whether previous executions were completed or not.
     */
    public function getFechaProximaAttribute(): ?\Carbon\Carbon
    {
        if (!$this->fecha_inicio) {
            return null;
        }

        if ($this->frecuencia === 'eventual') {
            return null;
        }

        $veces = (int) ($this->veces_al_anio ?? 1);

        // Si es una sola vez al año (o veces=1), siempre mostrar la fecha inicio
        // independientemente de si ya pasó o no.
        if ($veces === 1) {
            return $this->fecha_inicio;
        }

        $today = now()->startOfDay();
        $start = $this->fecha_inicio->copy()->startOfDay();

        // If start date is in the future or today, that's the next one
        if ($start->gte($today)) {
            return $this->fecha_inicio;
        }

        // Otherwise, iterate to find the first occurrence >= today
        $current = $start->copy();

        for ($i = 0; $i < $veces; $i++) {
            if ($current->gte($today)) {
                return $current;
            }

            // Advance
            match($this->frecuencia) {
                 'diario' => $current->addDay(),
                 'semanal' => $current->addWeek(),
                 'mensual' => $current->addMonth(),
                 'trimestral' => $current->addMonths(3),
                 'semestral' => $current->addMonths(6),
                 'anual' => $current->addYear(),
                 default => $current->addMonth(),
            };
        }

        // If all scheduled dates are in the past, return null (or last one?)
        // User implies "siguiente fecha disponible", if none available, maybe null.
        return null; 
    }
}
