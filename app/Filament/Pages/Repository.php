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
    public ?string $activeFolderId = null; // ID of the currently open folder (Program ID or Type string)
    public ?string $activeFolderName = null; // Name to display in breadcrumbs
    public ?string $search = ''; // Search query

    protected $queryString = [
        'viewMode',
        'activeFolderId',
        'search' => ['except' => ''],
    ];

    public function getViewData(): array
    {
        // Si hay una carpeta activa, mostrar su contenido
        if ($this->activeFolderId) {
            return $this->getFolderContent($this->activeFolderId);
        }

        // Si no, mostrar la vista raíz (Carpetas + Recientes)
        return $this->getRootContent();
    }

    protected function getRootContent(): array
    {
        $folders = [];
        $recentFiles = [];

        // Obtener carpetas según el modo de vista
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
                    'type' => 'program',
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
                    'id' => $type, // El ID es el string del tipo
                    'name' => ucfirst(str_replace('_', ' ', $type)),
                    'type' => 'type',
                    'count' => $count,
                    'icon' => 'heroicon-o-folder',
                    'color' => 'warning', // Amarillo como en la imagen
                ];
            }
        }

        // Obtener archivos recientes (Actividades ejecutadas con evidencia o Documentación)
        // Esto es un ejemplo, ajusta según tu lógica de "archivos"
        $recentFiles = ActivityExecution::query()
            ->whereNotNull('evidencia')
            ->where('evidencia', '!=', '[]')
            ->latest('updated_at')
            ->take(5)
            ->with(['activity.program'])
            ->get()
            ->map(function ($execution) {
                $files = [];
                if (is_array($execution->evidencia)) {
                    foreach ($execution->evidencia as $path) {
                        $files[] = [
                            'name' => basename($path),
                            'date' => $execution->updated_at->format('Y-m-d'),
                            'size' => 'N/A', // Opcional: obtener tamaño real si es posible
                            'type' => strtoupper(pathinfo($path, PATHINFO_EXTENSION)) . ' File',
                            'url' => Storage::disk('google')->url($path),
                            'execution_id' => $execution->id,
                        ];
                    }
                }
                return $files;
            })
            ->flatten(1)
            ->take(5);

        return [
            'folders' => $folders,
            'recentFiles' => $recentFiles,
            'isRoot' => true,
        ];
    }

    protected function getFolderContent($folderId): array
    {
        $files = [];
        $folderName = '';

        if ($this->viewMode === 'program') {
            $program = Program::find($folderId);
            $folderName = $program ? $program->nombre : 'Desconocido';
            
            // Obtener actividades/documentos de este programa
            $activities = Activity::where('program_id', $folderId)
                ->with(['executions' => fn($q) => $q->whereNotNull('evidencia')])
                ->when($this->search, fn ($q) => $q->where('nombre', 'like', "%{$this->search}%"))
                ->get();
        } else {
            $folderName = ucfirst(str_replace('_', ' ', $folderId));
            
            // Obtener actividades de este tipo
            $activities = Activity::where('tipo', $folderId)
                ->with(['executions' => fn($q) => $q->whereNotNull('evidencia')])
                ->when($this->search, fn ($q) => $q->where('nombre', 'like', "%{$this->search}%"))
                ->get();
        }

        // Transformar actividades en una lista de archivos/items
        foreach ($activities as $activity) {
            foreach ($activity->executions as $execution) {
                if (is_array($execution->evidencia)) {
                    foreach ($execution->evidencia as $path) {
                        $files[] = [
                            'name' => basename($path),
                            'activity_name' => $activity->nombre,
                            'date' => $execution->updated_at->format('Y-m-d'),
                            'type' => strtoupper(pathinfo($path, PATHINFO_EXTENSION)),
                            'url' => Storage::disk('google')->url($path),
                            'execution_id' => $execution->id,
                        ];
                    }
                }
            }
        }

        return [
            'files' => $files,
            'folderName' => $folderName,
            'isRoot' => false,
        ];
    }

    public function openFolder($id, $name)
    {
        $this->activeFolderId = $id;
        $this->activeFolderName = $name;
        $this->search = ''; // Limpiar búsqueda al entrar
    }

    public function goBack()
    {
        $this->activeFolderId = null;
        $this->activeFolderName = null;
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
                            $url = Storage::disk('google')->url($path);
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
