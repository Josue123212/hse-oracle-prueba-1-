<?php

namespace App\Filament\Resources\Drills\Widgets;

use App\Models\Drill;
use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Enums\ActivityState;

class DrillStatsOverview extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Simulacros', Drill::count())
                ->description('Registrados en el sistema')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary'),

            Stat::make('Programados', ActivityExecution::where('estado', ActivityState::PROGRAMADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'simulacro'))
                    ->count())
                ->description('Pendientes de ejecución')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Ejecutados', ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'simulacro'))
                    ->count())
                ->description('Completados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
