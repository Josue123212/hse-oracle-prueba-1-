<?php

namespace App\Filament\Resources\Inspections\Widgets;

use App\Models\Inspection;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Enums\ActivityState;

class InspectionStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pendientes', Inspection::where('estado', ActivityState::PROGRAMADO->value)->count())
                ->description('Por realizar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Vencidas', Inspection::where('estado', ActivityState::NO_CUMPLIO->value)->count())
                ->description('Requieren atención')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
            
            Stat::make('Ejecutadas (Mes)', Inspection::where('estado', ActivityState::EJECUTADO->value)
                    ->whereMonth('fecha_programada', now()->month)
                    ->count())
                ->description('Completadas este mes')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
