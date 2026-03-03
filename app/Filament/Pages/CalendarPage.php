<?php

namespace App\Filament\Pages;

use App\Models\ActivityExecution;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Collection;
use BackedEnum;

class CalendarPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'Calendario de Actividades';
    
    protected static ?string $title = 'Calendario de Actividades';

    protected static ?string $slug = 'calendar';

    protected string $view = 'filament.pages.calendar-page';

    public function getEvents(): string
    {
        return ActivityExecution::query()
            ->with(['activity.component.program', 'activity.location', 'activity.responsable'])
            ->get()
            ->map(function (ActivityExecution $execution) {
                return [
                    'id' => $execution->id,
                    'title' => $execution->activity->nombre ?? 'Actividad sin nombre',
                    'start' => $execution->fecha_programada->format('Y-m-d'),
                    'end' => $execution->fecha_programada->format('Y-m-d'),
                    'backgroundColor' => match ($execution->estado->value) {
                        'ejecutado' => '#10B981', // green-500
                        'programado' => '#F59E0B', // amber-500
                        'en_proceso' => '#3B82F6', // blue-500
                        'no_cumplio' => '#EF4444', // red-500
                        default => '#6B7280', // gray-500
                    },
                    'borderColor' => 'transparent',
                    'extendedProps' => [
                        'program' => $execution->activity->program->nombre ?? '-',
                        'status' => $execution->estado->getLabel(),
                        'location' => $execution->activity->location->nombre ?? '-',
                        'responsible' => $execution->activity->responsable->nombre ?? '-',
                    ],
                ];
            })
            ->toJson();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewEvent')
                ->label('Detalles de Ejecución')
                ->hidden() // Hidden from header, triggered via JS
                ->modalHeading(fn ($arguments) => ActivityExecution::find($arguments['record_id'])?->activity->nombre ?? 'Detalles de Ejecución')
                ->modalContent(function ($arguments) {
                    $record = ActivityExecution::find($arguments['record_id']);
                    return view('filament.pages.calendar-event-modal', ['record' => $record]);
                })
                ->modalSubmitAction(false)
                ->modalCancelAction(fn () => Action::make('close')->label('Cerrar')->close())
                ->extraModalFooterActions(function ($arguments) {
                    $record = ActivityExecution::find($arguments['record_id']);
                    return [
                        Action::make('viewMore')
                            ->label('Ver más / Editar')
                            ->url(fn () => \App\Filament\Resources\ActivityExecutions\ActivityExecutionResource::getUrl('index', ['tableAction' => 'edit', 'tableActionRecord' => $record->id]))
                            ->openUrlInNewTab()
                            ->button(),
                    ];
                })
                ->slideOver(),
        ];
    }
}
