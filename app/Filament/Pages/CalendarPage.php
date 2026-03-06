<?php

namespace App\Filament\Pages;

use App\Enums\ActivityState;
use App\Models\ActivityExecution;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Collection;
use BackedEnum;

use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;

class CalendarPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Calendario de Actividades';

    protected static ?string $title = 'Calendario de Actividades';

    protected static ?string $slug = 'calendar';

    protected string $view = 'filament.pages.calendar-page';

    public function mount(): void
    {
        // Notificaciones centralizadas (Service)
        app(\App\Services\ActivityNotificationService::class)->checkAndNotify();
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getEvents(): string
    {
        return ActivityExecution::query()
            ->with(['activity.component.program', 'activity.location', 'activity.responsable'])
            ->get()
            ->map(function (ActivityExecution $execution) {
                // Determine color based on Activity Type
                $color = match ($execution->activity->tipo ?? 'general') {
                    'inspeccion' => '#3B82F6', // Blue
                    'simulacro' => '#F59E0B', // Orange
                    'auditoria' => '#8B5CF6', // Purple
                    'capacitacion' => '#10B981', // Green
                    'incidente' => '#EF4444', // Red
                    'control_operacional' => '#06B6D4', // Cyan
                    'comite' => '#6366F1', // Indigo
                    default => '#6B7280', // Gray
                };

                return [
                    'id' => $execution->id,
                    'title' => strtoupper($execution->activity->tipo ?? 'Actividad'), // Showing Type as title to match image style, or use $execution->activity->nombre
                    'description' => $execution->activity->nombre ?? 'Sin nombre', // Add description for tooltip/modal
                    'start' => $execution->fecha_programada->format('Y-m-d'),
                    'end' => $execution->fecha_programada->format('Y-m-d'),
                    'backgroundColor' => $color,
                    'borderColor' => 'transparent',
                    'extendedProps' => [
                        'program' => $execution->activity->program->nombre ?? '-',
                        'status' => $execution->estado->getLabel(),
                        'location' => $execution->activity->location->nombre ?? '-',
                        'responsible' => $execution->activity->responsable->nombre ?? '-',
                        'type_label' => ucfirst($execution->activity->tipo ?? 'General'),
                        'real_name' => $execution->activity->nombre ?? 'Actividad sin nombre',
                    ],
                ];
            })
            ->toJson();
    }

    public function getUpcomingEventsProperty()
    {
        return ActivityExecution::query()
            ->with(['activity.location'])
            ->whereDate('fecha_programada', '>=', now())
            ->whereHas('activity') // Ensure activity exists
            ->orderBy('fecha_programada')
            ->limit(4)
            ->get();
    }

    public function getMonthStatsProperty()
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $total = ActivityExecution::query()
            ->whereBetween('fecha_programada', [$start, $end])
            ->count();

        $completed = ActivityExecution::query()
            ->whereBetween('fecha_programada', [$start, $end])
            ->where('estado', \App\Enums\ActivityState::EJECUTADO)
            ->count();

        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $percentage,
        ];
    }

    public function viewEventAction(): Action
    {
        return Action::make('viewEvent')
            ->label('Detalles de Ejecución')
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
            ->modalWidth('md')
            ->slideOver();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
