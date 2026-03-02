<?php

namespace App\Filament\Resources\Inspections\Widgets;

use App\Models\Inspection;
use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Enums\ActivityState;

class InspectionStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pendientes', ActivityExecution::where('estado', ActivityState::PROGRAMADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'inspeccion'))
                    ->count())
                ->description('Por realizar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Vencidas', ActivityExecution::where('estado', ActivityState::NO_CUMPLIO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'inspeccion'))
                    ->count())
                ->description('Requieren atención')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
            
            Stat::make('Ejecutadas (Mes)', ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'inspeccion'))
                    ->whereMonth('fecha_programada', now()->month)
                    ->count())
                ->description('Completadas este mes')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
