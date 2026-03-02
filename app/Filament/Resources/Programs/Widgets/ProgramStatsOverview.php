<?php

namespace App\Filament\Resources\Programs\Widgets;

use App\Models\Program;
use App\Models\Activity;
use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Enums\ActivityState;

class ProgramStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Programas', Program::count())
                ->description('Programas registrados')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
            
            Stat::make('Actividades Definidas', Activity::count())
                ->description('Total de actividades configuradas')
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('info'),

            Stat::make('Inspecciones Ejecutadas', ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'inspeccion'))
                    ->count())
                ->description('Cumplimiento operativo')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
