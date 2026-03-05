<?php

namespace App\Filament\Resources\OperationalControls\Widgets;

use App\Enums\ActivityState;
use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationalControlStatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Controles Programados Hoy', ActivityExecution::query()
                ->whereHas('activity', fn ($q) => $q->where('tipo', 'control_operacional'))
                ->whereDate('fecha_programada', now())
                ->count())
                ->description('Total para el día')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Ejecutados Hoy', ActivityExecution::query()
                ->whereHas('activity', fn ($q) => $q->where('tipo', 'control_operacional'))
                ->whereDate('fecha_programada', now())
                ->where('estado', ActivityState::EJECUTADO)
                ->count())
                ->description('Completados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pendientes Hoy', ActivityExecution::query()
                ->whereHas('activity', fn ($q) => $q->where('tipo', 'control_operacional'))
                ->whereDate('fecha_programada', now())
                ->where('estado', ActivityState::PROGRAMADO)
                ->count())
                ->description('Por realizar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
