<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\ActivityExecution;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class ActivitiesByTypeChart extends Widget
{
    protected string $view = 'filament.widgets.activities-by-type-chart';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1; // Position after the stats cards

    protected function getViewData(): array
    {
        // Define all activity types with their labels
        $types = [
            'general' => 'General',
            'auditoria' => 'Auditoría',
            'inspeccion' => 'Inspección',
            'capacitacion' => 'Capacitación',
            'simulacro' => 'Simulacro',
            'incidente' => 'Incidente',
            'comite' => 'Comité',
            'documentacion' => 'Documentación',
            'promocion' => 'Promoción',
            'control_operacional' => 'Control Operacional',
        ];

        // Get count of activity executions by activity type from DB
        $dbData = ActivityExecution::join('activities', 'activity_executions.activity_id', '=', 'activities.id')
            ->select('activities.tipo', DB::raw('count(*) as count'))
            ->groupBy('activities.tipo')
            ->pluck('count', 'activities.tipo')
            ->toArray();

        $labels = [];
        $values = [];

        foreach ($types as $key => $label) {
            $labels[] = $label;
            $values[] = $dbData[$key] ?? 0;
        }

        return [
            'chartData' => [
                'labels' => $labels,
                'values' => $values,
            ],
        ];
    }
}
