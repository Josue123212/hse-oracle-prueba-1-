<?php

namespace App\Filament\Resources\Committees\Widgets;

use App\Models\Committee;
use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Enums\ActivityState;

class CommitteeStatsOverview extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Comités', Committee::count())
                ->description('Registrados en el sistema')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Programados', ActivityExecution::where('estado', ActivityState::PROGRAMADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'comite'))
                    ->count())
                ->description('Pendientes de realización')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Realizados', ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'comite'))
                    ->count())
                ->description('Completados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
