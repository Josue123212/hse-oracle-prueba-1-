<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;

class FixActivityTypeInconsistencies extends Command
{
    protected $signature = 'fix:activity-types';
    protected $description = 'Fix inconsistencies where activities have child records of wrong types';

    public function handle()
    {
        $activities = Activity::all();
        $count = 0;

        foreach ($activities as $activity) {
            $this->info("Checking Activity ID: {$activity->id} Type: {$activity->tipo}");
            
            // Map types to relationships
            $relations = [
                'auditoria' => 'audit',
                'inspeccion' => 'inspection',
                'capacitacion' => 'training',
                'simulacro' => 'drill',
                'incidente' => 'incident',
                'comite' => 'committee',
                'documentacion' => 'documentation',
                'promocion' => 'promotion',
                'control_operacional' => 'operationalControl',
            ];

            foreach ($relations as $type => $relation) {
                // If the activity is NOT of this type, but has a record for this relation, delete it
                if ($activity->tipo !== $type) {
                    // Check if relation exists
                    // Note: accessing dynamic property for relation returns the model or null
                    if ($activity->$relation()->exists()) {
                        $this->warn("  Found orphan {$relation} record for activity {$activity->id} (Type: {$activity->tipo}). Deleting...");
                        $activity->$relation()->delete();
                        $count++;
                    }
                }
            }
        }

        $this->info("Fixed {$count} inconsistencies.");
    }
}
