<?php

namespace App\Filament\Resources\Incidents\Widgets;

use App\Models\Incident;
use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Enums\ActivityState;

class IncidentStatsOverview extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Incidentes', Incident::count())
                ->description('Registrados en el sistema')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('primary'),

            Stat::make('Pendientes', ActivityExecution::whereIn('estado', [ActivityState::PROGRAMADO, ActivityState::EN_PROCESO])
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'incidente'))
                    ->count())
                ->description('En proceso de investigación')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Cerrados', ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'incidente'))
                    ->count())
                ->description('Investigación completada')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
