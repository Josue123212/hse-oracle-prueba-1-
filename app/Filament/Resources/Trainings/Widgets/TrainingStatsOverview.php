<?php

namespace App\Filament\Resources\Trainings\Widgets;

use App\Models\Training;
use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Enums\ActivityState;

class TrainingStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Sesiones Programadas', ActivityExecution::where('estado', ActivityState::PROGRAMADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'capacitacion'))
                    ->count())
                ->description('Pendientes de realizar')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Sesiones Realizadas', ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'capacitacion'))
                    ->count())
                ->description('Completadas exitosamente')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
