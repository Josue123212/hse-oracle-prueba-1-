<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;
use App\Models\Audit;
use App\Models\Inspection;
use App\Models\Drill;
use App\Models\Training;
use App\Models\OperationalControl;
use App\Models\Incident;
use App\Models\Committee;
use App\Models\Documentation;
use App\Models\Promotion;

class SyncActivityDates extends Command
{
    protected $signature = 'activities:sync-dates';
    protected $description = 'Sync activity dates from child records';

    public function handle()
    {
        $this->info('Starting activity date sync...');

        // Audits
        $this->info('Syncing Audits...');
        Audit::whereNotNull('activity_id')->chunk(100, function ($audits) {
            foreach ($audits as $audit) {
                Activity::where('id', $audit->activity_id)->update([
                    'fecha_inicio' => $audit->fecha_programada,
                    'fecha_fin' => $audit->fecha_ejecucion ?? $audit->fecha_programada,
                ]);
            }
        });

        // Inspections
        $this->info('Syncing Inspections...');
        Inspection::whereNotNull('activity_id')->chunk(100, function ($inspections) {
            foreach ($inspections as $inspection) {
                Activity::where('id', $inspection->activity_id)->update([
                    'fecha_inicio' => $inspection->fecha_programada,
                    'fecha_fin' => $inspection->fecha_programada,
                ]);
            }
        });

        // Drills
        $this->info('Syncing Drills...');
        Drill::whereNotNull('activity_id')->chunk(100, function ($drills) {
            foreach ($drills as $drill) {
                Activity::where('id', $drill->activity_id)->update([
                    'fecha_inicio' => $drill->fecha_programada,
                    'fecha_fin' => $drill->fecha_ejecucion ?? $drill->fecha_programada,
                ]);
            }
        });

        // Trainings
        $this->info('Syncing Trainings...');
        Training::whereNotNull('activity_id')->chunk(100, function ($trainings) {
            foreach ($trainings as $training) {
                Activity::where('id', $training->activity_id)->update([
                    'fecha_inicio' => $training->fecha_programada,
                    'fecha_fin' => $training->fecha_ejecucion ?? $training->fecha_programada,
                ]);
            }
        });

        // Operational Controls
        $this->info('Syncing Operational Controls...');
        OperationalControl::whereNotNull('activity_id')->chunk(100, function ($controls) {
            foreach ($controls as $control) {
                Activity::where('id', $control->activity_id)->update([
                    'fecha_inicio' => $control->fecha_programada,
                    'fecha_fin' => $control->fecha_programada,
                ]);
            }
        });

        // Incidents
        $this->info('Syncing Incidents...');
        Incident::whereNotNull('activity_id')->chunk(100, function ($incidents) {
            foreach ($incidents as $incident) {
                Activity::where('id', $incident->activity_id)->update([
                    'fecha_inicio' => $incident->fecha_programada ?? $incident->fecha_ocurrencia,
                    'fecha_fin' => $incident->fecha_programada ?? $incident->fecha_ocurrencia,
                ]);
            }
        });

        // Committees
        $this->info('Syncing Committees...');
        Committee::whereNotNull('activity_id')->chunk(100, function ($committees) {
            foreach ($committees as $committee) {
                Activity::where('id', $committee->activity_id)->update([
                    'fecha_inicio' => $committee->fecha_programada,
                    'fecha_fin' => $committee->fecha_realizada ?? $committee->fecha_programada,
                ]);
            }
        });

        // Promotions
        $this->info('Syncing Promotions...');
        Promotion::whereNotNull('activity_id')->chunk(100, function ($promotions) {
            foreach ($promotions as $promotion) {
                Activity::where('id', $promotion->activity_id)->update([
                    'fecha_inicio' => $promotion->fecha_programada,
                    'fecha_fin' => $promotion->fecha_programada,
                ]);
            }
        });

        // Documentation
        $this->info('Syncing Documentation...');
        Documentation::whereNotNull('activity_id')->chunk(100, function ($docs) {
            foreach ($docs as $doc) {
                $activity = Activity::find($doc->activity_id);
                if ($activity) {
                     $start = $doc->fecha_programada ?? $doc->created_at;
                     $end = $doc->fecha_aprobacion ?? $doc->fecha_programada ?? $doc->created_at;
                     $activity->update([
                        'fecha_inicio' => $start,
                        'fecha_fin' => $end,
                     ]);
                }
            }
        });

        $this->info('Activity dates synced successfully!');
    }
}
