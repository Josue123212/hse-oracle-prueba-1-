<?php

namespace App\Filament\Resources\Audits\Widgets;

use App\Models\Audit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuditStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Auditorías', Audit::count())
                ->description('Registradas en el sistema')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary'),

            Stat::make('Programadas', Audit::where('estado', 'programado')->count())
                ->description('Pendientes de ejecución')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Ejecutadas', Audit::where('estado', 'ejecutado')->count())
                ->description('Completadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
