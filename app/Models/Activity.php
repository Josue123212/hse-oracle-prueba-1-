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

use App\Traits\FilteredByProgram;

class Activity extends Model
{
    use FilteredByProgram;

    public bool $is_creating_from_ref = false;

    protected static function booted(): void
    {
        // Model events handled by ActivityService
    }

    /*
    public function createLinkedModel(): void
    {
        // ... (removed)
    }

    protected function getModelAttributes(string $modelClass): array
    {
        // ... (removed)
    }
    */

    protected $fillable = [
        'program_component_id',
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
        'fecha_inicio',
        'fecha_fin',
        'proxima_ejecucion',
        'responsable_id',
        'responsable_delegado_id',
        'apoyo',
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
    ];

    public function component(): BelongsTo
    {
        return $this->belongsTo(ProgramComponent::class, 'program_component_id');
    }

    /**
     * Accesor para mantener compatibilidad (read-only)
     * $activity->program devolverá el programa padre del componente
     */
    public function getProgramAttribute(): ?Program
    {
        return $this->component?->program;
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'responsable_id');
    }

    public function responsableDelegado(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'responsable_delegado_id');
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
     * Get the next execution date from the database field.
     */
    public function getNextExecutionDateAttribute(): ?\Carbon\Carbon
    {
        return $this->proxima_ejecucion;
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

    /*
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
    */

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
             if (!$this->fecha_fin) {
                 $this->fecha_fin = now();
             }
         }
         
         $this->save();
     }

    /**
     * Get the formatted list of scheduled months/dates from executions.
     */
    public function getScheduledMonthsAttribute(): string
    {
        if ($this->frecuencia === 'eventual') {
            return 'Eventual' . ($this->detalle_frecuencia ? ': ' . $this->detalle_frecuencia : '');
        }

        if ($this->executions->isEmpty()) {
            return 'N/A';
        }

        // Get months from executions
        $months = $this->executions
            ->sortBy('fecha_programada')
            ->map(function ($execution) {
                return ucfirst($execution->fecha_programada->isoFormat('MMM'));
            })
            ->unique()
            ->values()
            ->all();

        return implode(', ', $months);
    }

    /**
     * Get the next upcoming execution date relative to today from database.
     */
    public function getFechaProximaAttribute(): ?\Carbon\Carbon
    {
        if ($this->frecuencia === 'eventual') {
            return null;
        }

        $today = now()->startOfDay();

        // Check executions
        $next = $this->executions()
            ->whereDate('fecha_programada', '>=', $today)
            ->orderBy('fecha_programada', 'asc')
            ->first();

        return $next ? $next->fecha_programada : null;
    }
}
