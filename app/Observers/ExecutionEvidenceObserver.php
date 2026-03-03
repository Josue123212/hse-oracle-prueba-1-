<?php

namespace App\Observers;

use App\Models\ExecutionEvidence;
use Illuminate\Support\Facades\Auth;

class ExecutionEvidenceObserver
{
    /**
     * Handle the ExecutionEvidence "deleting" event.
     */
    public function deleting(ExecutionEvidence $executionEvidence): void
    {
        // Audit Trail: Record who is deleting this evidence
        if (Auth::check()) {
            $executionEvidence->deleted_by = Auth::id();
            // We need to save this change because 'deleting' happens before the soft delete update
            $executionEvidence->saveQuietly();
        }
    }
}
