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

    public function getViewData(): array
    {
        $programs = null;
        $activitiesByType = null;

        if ($this->viewMode === 'program') {
            $programs = Program::query()
                ->whereNull('parent_id')
                ->with([
                    'activities.executions' => function ($query) {
                        $query->orderBy('fecha_programada', 'desc');
                    },
                    'children' => function ($query) {
                        $query->with(['activities.executions' => function ($q) {
                            $q->orderBy('fecha_programada', 'desc');
                        }]);
                    }
                ])
                ->get();
        } else {
            $activitiesByType = Activity::with(['executions' => function ($query) {
                $query->orderBy('fecha_programada', 'desc');
            }])->get()->groupBy('tipo');
        }

        return [
            'programs' => $programs,
            'activitiesByType' => $activitiesByType,
        ];
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
