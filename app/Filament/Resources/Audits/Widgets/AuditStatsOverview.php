<?php

namespace App\Filament\Resources\Audits\Widgets;

use App\Models\Audit;
use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Enums\ActivityState;

class AuditStatsOverview extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Auditorías', Audit::count())
                ->description('Registradas en el sistema')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary'),

            Stat::make('Programadas', ActivityExecution::where('estado', ActivityState::PROGRAMADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'auditoria'))
                    ->count())
                ->description('Pendientes de ejecución')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Ejecutadas', ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'auditoria'))
                    ->count())
                ->description('Completadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
