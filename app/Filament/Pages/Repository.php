<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Program;
use App\Models\ActivityExecution;
use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use UnitEnum;
use BackedEnum;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class Repository extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = 'Almacenamiento';

    protected static ?string $navigationLabel = 'Repositorio';

    protected static ?string $title = 'Repositorio de Documentos';

    protected string $view = 'filament.pages.repository';

    public string $viewMode = 'program'; // 'program' or 'type'
    public ?string $activeFolderId = null; // ID of the currently open folder
    public ?string $activeFolderName = null; // Name to display in breadcrumbs
    public ?string $activeFolderType = null; // 'program', 'component', 'type', 'activity'
    public array $breadcrumbs = []; // Stack of navigation history
    public ?string $search = ''; // Search query
    
    // Preview Modal State
    public ?string $uploadedFileUrl = null;
    public ?string $uploadedFilePath = null;

    protected $queryString = [
        'viewMode',
        'activeFolderId',
        'activeFolderType',
        'search' => ['except' => ''],
    ];

    public function getViewData(): array
    {
        // Si hay una carpeta activa, mostrar su contenido según el tipo
        if ($this->activeFolderId && $this->activeFolderType) {
            return $this->getFolderContent($this->activeFolderId, $this->activeFolderType);
        }

        // Si no, mostrar la vista raíz
        return $this->getRootContent();
    }

    protected function getRootContent(): array
    {
        $folders = [];
        $recentFiles = [];

        if ($this->viewMode === 'program') {
            $programs = Program::query()
                ->whereNull('parent_id')
                ->when($this->search, fn ($q) => $q->where('nombre', 'like', "%{$this->search}%"))
                ->withCount(['activities'])
                ->get();

            foreach ($programs as $program) {
                $folders[] = [
                    'id' => $program->id,
                    'name' => $program->nombre,
                    'type' => 'program', // Next level: Components of this program
                    'count' => $program->activities_count,
                    'icon' => 'heroicon-o-folder',
                    'color' => 'warning',
                ];
            }
        } else {
            // Por tipo de actividad
            $types = Activity::select('tipo')
                ->distinct()
                ->when($this->search, fn ($q) => $q->where('tipo', 'like', "%{$this->search}%"))
                ->get()
                ->pluck('tipo');

            foreach ($types as $type) {
                $count = Activity::where('tipo', $type)->count();
                $folders[] = [
                    'id' => $type,
                    'name' => ucfirst(str_replace('_', ' ', $type)),
                    'type' => 'type', // Next level: Activities of this type
                    'count' => $count,
                    'icon' => 'heroicon-o-folder',
                    'color' => 'warning',
                ];
            }
        }

        // Obtener archivos recientes (mismo lógica que antes)
        $recentFiles = $this->getRecentFiles();

        return [
            'folders' => $folders,
            'recentFiles' => $recentFiles,
            'isRoot' => true,
            'currentType' => 'root',
        ];
    }

    protected function getFolderContent($folderId, $folderType): array
    {
        $folders = [];
        $files = [];
        
        switch ($folderType) {
            case 'program':
                // List Root Components of this Program
                $components = \App\Models\ProgramComponent::where('program_id', $folderId)
                    ->whereNull('parent_id')
                    ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->withCount(['activities', 'children'])
                    ->get();

                foreach ($components as $component) {
                    $folders[] = [
                        'id' => $component->id,
                        'name' => $component->name,
                        'type' => 'component', // Next level: Sub-components or Activities
                        'count' => $component->activities_count + $component->children_count,
                        'icon' => 'heroicon-o-folder',
                        'color' => 'info',
                    ];
                }
                break;

            case 'component':
                // List Sub-components
                $subComponents = \App\Models\ProgramComponent::where('parent_id', $folderId)
                    ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->withCount(['activities', 'children'])
                    ->get();

                foreach ($subComponents as $component) {
                    $folders[] = [
                        'id' => $component->id,
                        'name' => $component->name,
                        'type' => 'component',
                        'count' => $component->activities_count + $component->children_count,
                        'icon' => 'heroicon-o-folder',
                        'color' => 'info',
                    ];
                }

                // List Activities of this Component
                $activities = Activity::where('program_component_id', $folderId)
                    ->when($this->search, fn ($q) => $q->where('nombre', 'like', "%{$this->search}%"))
                    ->withCount(['executions' => fn($q) => $q->whereNotNull('evidencia')])
                    ->get();

                foreach ($activities as $activity) {
                    $folders[] = [
                        'id' => $activity->id,
                        'name' => $activity->nombre,
                        'type' => 'activity', // Next level: Files of this activity
                        'count' => $activity->executions_count, // Approximate file count
                        'icon' => 'heroicon-o-document-duplicate',
                        'color' => 'success',
                    ];
                }
                break;

            case 'type':
                // List Activities of this Type
                $activities = Activity::where('tipo', $folderId)
                    ->when($this->search, fn ($q) => $q->where('nombre', 'like', "%{$this->search}%"))
                    ->withCount(['executions' => fn($q) => $q->whereNotNull('evidencia')])
                    ->get();

                foreach ($activities as $activity) {
                    $folders[] = [
                        'id' => $activity->id,
                        'name' => $activity->nombre,
                        'type' => 'activity', // Next level: Files
                        'count' => $activity->executions_count,
                        'icon' => 'heroicon-o-document-duplicate',
                        'color' => 'success',
                    ];
                }
                break;

            case 'activity':
                // List Files (Executions) of this Activity
                $activity = Activity::with(['executions' => fn($q) => $q->whereNotNull('evidencia')])
                    ->find($folderId);
                
                if ($activity) {
                    foreach ($activity->executions as $execution) {
                        $evidences = $execution->evidencia;
                        if (is_string($evidences)) {
                            $decoded = json_decode($evidences, true);
                            $evidences = is_array($decoded) ? $decoded : [$evidences];
                        }
                        
                        if (is_array($evidences)) {
                            foreach ($evidences as $path) {
                                // Skip if search is active and doesn't match filename
                                if ($this->search && stripos(basename($path), $this->search) === false) {
                                    continue;
                                }

                                try {
                                    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                                    $disk = Storage::disk('google');
                                    $url = $disk->url($path);
                                    $mime = $disk->mimeType($path);
                                    $size = $disk->size($path);
                                } catch (\Exception $e) {
                                    $url = '#';
                                    $mime = 'application/octet-stream';
                                    $size = 0;
                                }
                                
                                $files[] = [
                                    'name' => basename($path),
                                    'path' => $path, // Needed for preview
                                    'activity_name' => $activity->nombre,
                                    'date' => $execution->updated_at->format('Y-m-d'),
                                    'type' => strtoupper(pathinfo($path, PATHINFO_EXTENSION)),
                                    'mime' => $mime,
                                    'size' => $size,
                                    'url' => $url,
                                    'execution_id' => $execution->id,
                                ];
                            }
                        }
                    }
                }
                break;
        }

        return [
            'folders' => $folders,
            'files' => $files,
            'isRoot' => false,
            'currentType' => $folderType,
        ];
    }

    public function openFolder($id, $name, $type)
    {
        // Push current state to history before changing
        if ($this->activeFolderId) {
            $this->breadcrumbs[] = [
                'id' => $this->activeFolderId,
                'name' => $this->activeFolderName,
                'type' => $this->activeFolderType,
            ];
        } else {
            // Pushing root state placeholder if needed, but usually root is empty breadcrumb
        }

        $this->activeFolderId = $id;
        $this->activeFolderName = $name;
        $this->activeFolderType = $type;
        $this->search = ''; 
    }

    public function goBack()
    {
        if (count($this->breadcrumbs) > 0) {
            $previous = array_pop($this->breadcrumbs);
            $this->activeFolderId = $previous['id'];
            $this->activeFolderName = $previous['name'];
            $this->activeFolderType = $previous['type'];
        } else {
            // Back to root
            $this->activeFolderId = null;
            $this->activeFolderName = null;
            $this->activeFolderType = null;
            $this->breadcrumbs = [];
        }
        $this->search = '';
    }

    public function selectFile($path)
    {
        try {
            $this->uploadedFilePath = $path;
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('google');
            $this->uploadedFileUrl = $disk->url($path);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'status' => 'danger',
                'message' => 'Error al cargar el archivo: ' . $e->getMessage()
            ]);
        }
    }

    protected function getRecentFiles()
    {
        return ActivityExecution::query()
            ->whereNotNull('evidencia')
            ->where('evidencia', '!=', '[]')
            ->latest('updated_at')
            ->take(5)
            ->with(['activity.component.program'])
            ->get()
            ->map(function ($execution) {
                $files = [];
                $evidences = $execution->evidencia;
                if (is_string($evidences)) {
                    $decoded = json_decode($evidences, true);
                    $evidences = is_array($decoded) ? $decoded : [$evidences];
                }

                if (is_array($evidences)) {
                    foreach ($evidences as $path) {
                        try {
                            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                            $disk = Storage::disk('google');
                            $url = $disk->url($path);
                        } catch (\Exception $e) {
                            $url = '#';
                        }
                        
                        $files[] = [
                            'name' => basename($path),
                            'path' => $path,
                            'date' => $execution->updated_at->format('Y-m-d'),
                            'size' => 'N/A',
                            'type' => strtoupper(pathinfo($path, PATHINFO_EXTENSION)),
                            'url' => $url,
                            'execution_id' => $execution->id,
                        ];
                    }
                }
                return $files;
            })
            ->flatten(1)
            ->take(5);
    }
    public function resetNavigation()
    {
        $this->activeFolderId = null;
        $this->activeFolderName = null;
        $this->activeFolderType = null;
        $this->breadcrumbs = [];
        $this->search = '';
    }

    public function viewDocumentAction(): Action
    {
        return Action::make('viewDocument')
            ->label('Ver Evidencias')
            ->icon('heroicon-o-eye')
            ->size('sm')
            ->modalHeading('Vista Previa de Evidencias')
            ->modalContent(function (array $arguments) {
                $executionId = $arguments['execution_id'] ?? null;
                $record = ActivityExecution::find($executionId);
                $files = [];

                if ($record && is_array($record->evidencia)) {
                    foreach ($record->evidencia as $path) {
                        try {
                            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                            $disk = Storage::disk('google');
                            $url = $disk->url($path);
                            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            
                            $files[] = [
                                'path' => $path,
                                'url' => $url,
                                'name' => basename($path),
                                'extension' => $extension,
                            ];
                        } catch (\Exception $e) {
                            // Ignorar errores al obtener URL
                        }
                    }
                }

                return view('filament.actions.evidence-modal', ['files' => $files]);
            })
            ->modalWidth('screen')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar');
    }
}
