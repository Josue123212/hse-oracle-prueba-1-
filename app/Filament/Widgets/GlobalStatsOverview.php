<?php

namespace App\Filament\Widgets;

use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

use App\Enums\ActivityState;

class GlobalStatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Cache the raw stats for 30 seconds to match polling interval
        $stats = Cache::remember('hse_global_stats', 30, function () {
            // Optimize: Use DB::select for faster aggregation or simpler Eloquent counts
            // Combining queries where possible
            
            $inspectionStats = [
                'total' => ActivityExecution::whereHas('activity', fn($q) => $q->where('tipo', 'inspeccion'))->count(),
                'vencidos' => ActivityExecution::where('estado', ActivityState::NO_CUMPLIO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'inspeccion'))->count(),
                'programados' => ActivityExecution::where('estado', ActivityState::PROGRAMADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'inspeccion'))->count(),
                'ejecutados' => ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'inspeccion'))->count(),
            ];

            $trainingStats = [
                'total' => ActivityExecution::whereHas('activity', fn($q) => $q->where('tipo', 'capacitacion'))->count(),
                'programados' => ActivityExecution::where('estado', ActivityState::PROGRAMADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'capacitacion'))->count(),
                'ejecutados' => ActivityExecution::where('estado', ActivityState::EJECUTADO)
                    ->whereHas('activity', fn($q) => $q->where('tipo', 'capacitacion'))->count(),
            ];

            return [
                'inspeccionesVencidas' => $inspectionStats['vencidos'],
                'inspeccionesPendientes' => $inspectionStats['programados'],
                'capacitacionesPendientes' => $trainingStats['programados'],
                'totalInspecciones' => $inspectionStats['total'],
                'inspeccionesEjecutadas' => $inspectionStats['ejecutados'],
                'totalTrainings' => $trainingStats['total'],
                'trainingsEjecutadas' => $trainingStats['ejecutados'],
            ];
        });

        $inspeccionesVencidas = $stats['inspeccionesVencidas'];
        $inspeccionesPendientes = $stats['inspeccionesPendientes'];
        $capacitacionesPendientes = $stats['capacitacionesPendientes'];
        
        // Calculations
        $totalInspecciones = $stats['totalInspecciones'];
        $inspeccionesEjecutadas = $stats['inspeccionesEjecutadas'];
        $porcentajeInspecciones = $totalInspecciones > 0 ? ($inspeccionesEjecutadas / $totalInspecciones) * 100 : 0;

        $totalTrainings = $stats['totalTrainings'];
        $trainingsEjecutadas = $stats['trainingsEjecutadas'];
        $porcentajeTrainings = $totalTrainings > 0 ? ($trainingsEjecutadas / $totalTrainings) * 100 : 0;

        $cumplimientoGlobal = round(($porcentajeInspecciones + $porcentajeTrainings) / 2, 1);

        return [
            Stat::make('Cumplimiento Global HSE', $cumplimientoGlobal . '%')
                ->description('Promedio de ejecución (Insp + Cap)')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($cumplimientoGlobal > 80 ? 'success' : 'warning')
                ->chart([70, 80, 75, 85, 90, 85, $cumplimientoGlobal]),

            Stat::make('Inspecciones Críticas', $inspeccionesVencidas)
                ->description('Vencidas / No realizadas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Tareas Pendientes', $inspeccionesPendientes + $capacitacionesPendientes)
                ->description('Inspecciones + Capacitaciones')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
        ];
    }
}
