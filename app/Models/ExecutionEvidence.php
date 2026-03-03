<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExecutionEvidence extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'execution_evidences';

    protected $fillable = [
        'execution_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'file_hash',
        'uploaded_by',
        'uploaded_at',
        'revoked_at',
        'revoked_by',
        'revoke_reason',
        'is_replacement',
        'deleted_by'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_replacement' => 'boolean',
    ];

    /**
     * Get the execution that owns the evidence.
     */
    public function execution(): BelongsTo
    {
        return $this->belongsTo(ActivityExecution::class, 'execution_id');
    }

    /**
     * Get the user who uploaded the evidence.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user who revoked the evidence.
     */
    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
    
    /**
     * Get the user who deleted the evidence (audit trail).
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope a query to only include active (non-revoked) evidences.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }
}
