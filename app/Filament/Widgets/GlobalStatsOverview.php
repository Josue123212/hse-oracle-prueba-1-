<?php

namespace App\Filament\Widgets;

use App\Models\ActivityExecution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

use App\Enums\ActivityState;

class GlobalStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Obtener el programa seleccionado de la sesión
        $programId = session('hse_program_id');
        $cacheKey = 'hse_global_stats_' . ($programId ?? 'all');

        // Cache the raw stats for 30 seconds to match polling interval
        $stats = Cache::remember($cacheKey, 30, function () {
            $now = now();
            $startOfYear = $now->copy()->startOfYear();
            // $endOfYear = $now->copy()->endOfYear();

            // 1. Compliance (Year to Date) - All activities
            // Denominator: Activities scheduled up to today (inclusive)
            $dueQuery = ActivityExecution::whereBetween('fecha_programada', [$startOfYear, $now->endOfDay()]);
            $dueCount = (clone $dueQuery)->count();
            
            // Numerator: Executed activities from the due set
            $executedDueCount = (clone $dueQuery)->where('estado', ActivityState::EJECUTADO)->count();
            
            $compliance = $dueCount > 0 ? round(($executedDueCount / $dueCount) * 100, 1) : 0;

            // 2. Critical Overdue (Obligatory activities that are overdue or failed)
            $criticalOverdue = ActivityExecution::whereHas('activity', function ($q) {
                    $q->where('es_obligatoria', true);
                })
                ->where(function($query) use ($now) {
                    // Explicitly failed
                    $query->where('estado', ActivityState::NO_CUMPLIO)
                          // Or scheduled in the past (strictly before today) and not done
                          ->orWhere(function($q) use ($now) {
                              $q->whereIn('estado', [ActivityState::PROGRAMADO, ActivityState::EN_PROCESO])
                                ->where('fecha_programada', '<', $now->startOfDay());
                          });
                })->count();

            // 3. Pending Tasks (All pending regardless of type)
            // Includes future scheduled and overdue pending
            $pending = ActivityExecution::whereIn('estado', [ActivityState::PROGRAMADO, ActivityState::EN_PROCESO])->count();

            return [
                'compliance' => $compliance,
                'criticalOverdue' => $criticalOverdue,
                'pending' => $pending,
            ];
        });

        $compliance = $stats['compliance'];
        $criticalOverdue = $stats['criticalOverdue'];
        $pending = $stats['pending'];

        return [
            Stat::make('Cumplimiento Global HSE', $compliance . '%')
                ->description('Ejecución vs Programado (YTD)')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($compliance > 80 ? 'success' : ($compliance > 50 ? 'warning' : 'danger'))
                ->chart([70, 80, 75, 85, 90, 85, $compliance]),

            Stat::make('Actividades Críticas Vencidas', $criticalOverdue)
                ->description('Obligatorias vencidas / no cumplidas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($criticalOverdue > 0 ? 'danger' : 'success'),

            Stat::make('Tareas Pendientes', $pending)
                ->description('Total actividades por ejecutar')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
        ];
    }
}
