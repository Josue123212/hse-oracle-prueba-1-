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
use App\Models\ActivityExecution;
use App\Observers\ActivityExecutionObserver;
use Illuminate\Support\Facades\Storage;
use Google\Client;
use Google\Service\Drive;
use Masbug\Flysystem\GoogleDriveAdapter;
use League\Flysystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;

use Illuminate\Support\Facades\Log;

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
        Storage::extend('google', function($app, $config) {
            $options = [];

            if (!empty($config['teamDriveId'] ?? null)) {
                $options['teamDriveId'] = $config['teamDriveId'];
            }

            $client = new Client();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->refreshToken($config['refreshToken']);
            
            $service = new Drive($client);
            $folderId = $config['folderId'] ?? null;
            
            if ($folderId) {
                // Usamos 'sharedFolderId' en las opciones para forzar que el adaptador
                // trate esto como un ID raíz, independientemente del nombre.
                $options['sharedFolderId'] = $folderId;
            }
            
            Log::info("Inicializando Google Drive Adapter. Root Folder ID configurado: " . ($folderId ?? 'Raíz'));

            // Pasamos null como segundo parámetro para que use la opción sharedFolderId como raíz
            $adapter = new GoogleDriveAdapter($service, null, $options);
            
            $driver = new Filesystem($adapter);

            return new FilesystemAdapter($driver, $adapter, $config);
        });

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
        ActivityExecution::observe(ActivityExecutionObserver::class);
    }
}
