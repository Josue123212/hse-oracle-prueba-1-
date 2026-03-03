<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramComponent extends Model
{
    protected $fillable = [
        'program_id',
        'parent_id',
        'name',
        'objetivo', // Specific objective
        'type', // 'subprograma', 'elemento'
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProgramComponent::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProgramComponent::class, 'parent_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Devuelve el nombre jerárquico completo (recursivo)
     */
    public function getFullNameAttribute(): string
    {
        $parts = [$this->name];
        $curr = $this->parent;
        while ($curr) {
            array_unshift($parts, $curr->name);
            $curr = $curr->parent;
        }
        return implode(' > ', $parts);
    }
}
