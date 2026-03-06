<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Google\Client;
use Google\Service\Drive;
use Masbug\Flysystem\GoogleDriveAdapter;
use League\Flysystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;

use Illuminate\Support\Facades\Log;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

// Models
use App\Models\Activity;
use App\Models\ActivityExecution;
use App\Models\Audit;
use App\Models\Committee;
use App\Models\Documentation;
use App\Models\Drill;
use App\Models\ExecutionEvidence;
use App\Models\Incident;
use App\Models\Inspection;
use App\Models\Location;
use App\Models\Message;
use App\Models\Alert;
use App\Models\OperationalControl;
use App\Models\Position;
use App\Models\PositionType;
use App\Models\Program;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Supervisor;
use App\Models\Training;
use App\Models\TrainingAttendance;
use App\Models\TrainingMaterial;
use App\Models\User;

// Policies
use App\Policies\ActivityPolicy;
use App\Policies\ActivityExecutionPolicy;
use App\Policies\AuditPolicy;
use App\Policies\CommitteePolicy;
use App\Policies\DocumentationPolicy;
use App\Policies\DrillPolicy;
use App\Policies\ExecutionEvidencePolicy;
use App\Policies\IncidentPolicy;
use App\Policies\InspectionPolicy;
use App\Policies\LocationPolicy;
use App\Policies\MessagePolicy;
use App\Policies\AlertPolicy;
use App\Policies\OperationalControlPolicy;
use App\Policies\PositionPolicy;
use App\Policies\PositionTypePolicy;
use App\Policies\ProgramPolicy;
use App\Policies\PromotionPolicy;
use App\Policies\RolePolicy;
use App\Policies\SupervisorPolicy;
use App\Policies\TrainingPolicy;
use App\Policies\TrainingAttendancePolicy;
use App\Policies\TrainingMaterialPolicy;
use App\Policies\UserPolicy;

// Observers
use App\Observers\ExecutionEvidenceObserver;

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
        // Super Admin Gate (Bypass all policies for Admin)
        Gate::before(function ($user, $ability) {
            return $user->role_id === 1 ? true : null;
        });

        // Observers Registration
        // ActivityExecutionObserver removed to centralize logic in ActivityService (Hallazgo 02)
        ExecutionEvidence::observe(ExecutionEvidenceObserver::class);

        // Explicit Policy Registration (Hallazgo 08 requirement)
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(ActivityExecution::class, ActivityExecutionPolicy::class);
        Gate::policy(Audit::class, AuditPolicy::class);
        Gate::policy(Committee::class, CommitteePolicy::class);
        Gate::policy(Documentation::class, DocumentationPolicy::class);
        Gate::policy(Drill::class, DrillPolicy::class);
        Gate::policy(ExecutionEvidence::class, ExecutionEvidencePolicy::class);
        Gate::policy(Incident::class, IncidentPolicy::class);
        Gate::policy(Inspection::class, InspectionPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
        Gate::policy(Alert::class, AlertPolicy::class);
        Gate::policy(OperationalControl::class, OperationalControlPolicy::class);
        Gate::policy(Position::class, PositionPolicy::class);
        Gate::policy(PositionType::class, PositionTypePolicy::class);
        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(Promotion::class, PromotionPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Supervisor::class, SupervisorPolicy::class);
        Gate::policy(Training::class, TrainingPolicy::class);
        Gate::policy(TrainingAttendance::class, TrainingAttendancePolicy::class);
        Gate::policy(TrainingMaterial::class, TrainingMaterialPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

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

        // Sticky Header for All Admin Pages
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn () => view('components.sticky-header')
        );

        // Register Custom Glass Modal globally
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn () => view('components.custom-glass-modal')
        );

        // Helper for Custom Glass Success Modal
        $dispatchSuccess = function ($action, $message) {
            $action->successNotification(null); // Disable default toast
            // Check if getLivewire exists (it should in Filament context)
            if (method_exists($action, 'getLivewire')) {
                $action->getLivewire()->dispatch('open-custom-modal', [
                    'type' => 'success',
                    'title' => '¡Éxito!',
                    'body' => $message,
                ]);
            }
        };

        // Global Action Configurations
        CreateAction::configureUsing(function (CreateAction $action) use ($dispatchSuccess) {
            $action->after(fn () => $dispatchSuccess($action, 'Registro creado correctamente'));
        });
        
        EditAction::configureUsing(function (EditAction $action) use ($dispatchSuccess) {
            $action->after(fn () => $dispatchSuccess($action, 'Registro actualizado correctamente'));
        });

        DeleteAction::configureUsing(function (DeleteAction $action) use ($dispatchSuccess) {
            $action
                ->requiresConfirmation()
                ->modalHeading('Eliminar registro')
                ->modalDescription('¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar')
                ->after(fn () => $dispatchSuccess($action, 'Registro eliminado correctamente'));
        });

        DeleteBulkAction::configureUsing(function (DeleteBulkAction $action) use ($dispatchSuccess) {
            $action
                ->requiresConfirmation()
                ->modalHeading('Eliminar registros')
                ->modalDescription('¿Estás seguro de que deseas eliminar los registros seleccionados? Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar todo')
                ->after(fn () => $dispatchSuccess($action, 'Registros eliminados correctamente'));
        });
    }
}
