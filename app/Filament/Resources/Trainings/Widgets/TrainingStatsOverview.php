<?php

namespace App\Filament\Resources\Trainings\Widgets;

use App\Models\Training;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TrainingStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Sesiones Programadas', Training::where('estado', 'programado')->count())
                ->description('Pendientes de realizar')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Sesiones Realizadas', Training::where('estado', 'ejecutado')->count())
                ->description('Completadas exitosamente')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
