<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\Inspection;
use App\Models\Training;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            
            $inspectionStats = Inspection::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN estado = '" . ActivityState::NO_CUMPLIO->value . "' THEN 1 ELSE 0 END) as vencidos,
                SUM(CASE WHEN estado = '" . ActivityState::PROGRAMADO->value . "' THEN 1 ELSE 0 END) as programados,
                SUM(CASE WHEN estado = '" . ActivityState::EJECUTADO->value . "' THEN 1 ELSE 0 END) as ejecutados
            ")->first();

            $trainingStats = Training::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN estado = '" . ActivityState::PROGRAMADO->value . "' THEN 1 ELSE 0 END) as programados,
                SUM(CASE WHEN estado = '" . ActivityState::EJECUTADO->value . "' THEN 1 ELSE 0 END) as ejecutados
            ")->first();

            return [
                'inspeccionesVencidas' => $inspectionStats->vencidos ?? 0,
                'inspeccionesPendientes' => $inspectionStats->programados ?? 0,
                'capacitacionesPendientes' => $trainingStats->programados ?? 0,
                'totalInspecciones' => $inspectionStats->total ?? 0,
                'inspeccionesEjecutadas' => $inspectionStats->ejecutados ?? 0,
                'totalTrainings' => $trainingStats->total ?? 0,
                'trainingsEjecutadas' => $trainingStats->ejecutados ?? 0,
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
