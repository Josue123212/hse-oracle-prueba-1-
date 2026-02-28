<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'anio',
        'estado',
        'supervisor_id',
        'parent_id', // Add parent_id to fillable
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class);
    }

    // Self-referencing relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Program::class, 'parent_id');
    }

    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class);
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(Documentation::class);
    }

    public function drills(): HasMany
    {
        return $this->hasMany(Drill::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function operationalControls(): HasMany
    {
        return $this->hasMany(OperationalControl::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class);
    }
}
