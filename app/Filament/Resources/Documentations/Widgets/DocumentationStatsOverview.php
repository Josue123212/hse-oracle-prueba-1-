<?php

namespace App\Filament\Resources\Documentations\Widgets;

use App\Models\Documentation;
use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Enums\ActivityState;

class DocumentationStatsOverview extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Documentos', Documentation::count())
                ->description('Registrados en el sistema')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Pendientes', ActivityExecution::where('estado', ActivityState::PROGRAMADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'documentacion')
                          ->orWhereHas('documentation'))
                    ->count())
                ->description('Revisiones/Actualizaciones pendientes')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Completados', ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'documentacion')
                          ->orWhereHas('documentation'))
                    ->count())
                ->description('Revisiones completadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
