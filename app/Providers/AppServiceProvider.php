<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Audit;
use App\Models\Inspection;
use App\Models\Training;
use App\Models\Drill;
use App\Models\Incident;
use App\Models\Committee;
use App\Models\Documentation;
use App\Models\Promotion;
use App\Models\Activity;
use App\Models\OperationalControl;
use App\Observers\AuditObserver;
use App\Observers\InspectionObserver;
use App\Observers\TrainingObserver;
use App\Observers\DrillObserver;
use App\Observers\IncidentObserver;
use App\Observers\CommitteeObserver;
use App\Observers\DocumentationObserver;
use App\Observers\PromotionObserver;
use App\Observers\ActivityObserver;
use App\Observers\OperationalControlObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Audit::observe(AuditObserver::class);
        Inspection::observe(InspectionObserver::class);
        Training::observe(TrainingObserver::class);
        Drill::observe(DrillObserver::class);
        Incident::observe(IncidentObserver::class);
        Committee::observe(CommitteeObserver::class);
        Documentation::observe(DocumentationObserver::class);
        Promotion::observe(PromotionObserver::class);
        OperationalControl::observe(OperationalControlObserver::class);
        Activity::observe(ActivityObserver::class);
    }
}
