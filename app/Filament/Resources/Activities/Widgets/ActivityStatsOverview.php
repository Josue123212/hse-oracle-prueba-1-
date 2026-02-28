<?php

namespace App\Filament\Resources\Activities\Widgets;

use App\Models\Activity;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivityStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Actividades', Activity::count())
                ->description('Registradas en el sistema')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Obligatorias', Activity::where('es_obligatoria', true)->count())
                ->description('De cumplimiento forzoso')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),
            
            Stat::make('Meta Promedio', number_format(Activity::avg('meta') ?? 0, 1) . '%')
                ->description('Expectativa de cumplimiento')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
        ];
    }
}
