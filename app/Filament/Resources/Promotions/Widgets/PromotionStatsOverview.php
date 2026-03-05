<?php

namespace App\Filament\Resources\Promotions\Widgets;

use App\Models\Promotion;
use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Enums\ActivityState;

class PromotionStatsOverview extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Promociones', Promotion::count())
                ->description('Campañas registradas')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),

            Stat::make('Programadas', ActivityExecution::where('estado', ActivityState::PROGRAMADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'promocion'))
                    ->count())
                ->description('Pendientes de lanzamiento')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Realizadas', ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'promocion'))
                    ->count())
                ->description('Completadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
