<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Programa {{ $program->codigo }}</title>
    <style>
        @page { size: A3 landscape; margin: 100px 25px; }
        header { position: fixed; top: -80px; left: 0px; right: 0px; height: 80px; border-bottom: 2px solid #ddd; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
        body { font-family: 'Calibri', 'Arial', sans-serif; font-size: 10px; }
        
        /* Header Layout */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { vertical-align: middle; border: 1px solid #000; padding: 5px; }
        .logo-cell { width: 20%; text-align: center; }
        .title-cell { width: 60%; text-align: center; background-color: #f2f2f2; }
        .info-cell { width: 20%; font-size: 9px; }
        
        /* Main Content */
        .section-title { font-weight: bold; background-color: #333; color: white; padding: 5px; margin-top: 10px; margin-bottom: 5px; font-size: 11px; }
        .content-box { border: 1px solid #000; padding: 5px; margin-bottom: 10px; min-height: 40px; }
        
        /* Data Table */
        .data-table { width: 100%; border-collapse: collapse; font-size: 10px; table-layout: fixed; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; word-wrap: break-word; }
        .data-table th { background-color: #003366; color: white; font-weight: bold; font-size: 9px; }
        
        /* Column Widths */
        .w-obj { width: 14%; }
        .w-act { width: 23%; }
        .w-meta { width: 3%; }
        .w-sede { width: 6%; }
        .w-resp { width: 6%; }
        .w-del { width: 6%; }
        .w-apo { width: 6%; }
        .w-freq { width: 5%; }
        .w-month { width: 2.3%; font-size: 8px; } /* 12 months * 2.3% = ~27.6% */
        .w-cump { width: 3%; }
        .w-obs { width: 6.4%; }
        
        .group-header { background-color: #666; color: white; text-align: left; padding-left: 10px; font-weight: bold; }
        .group-header-row td { background-color: #666; color: white; font-weight: bold; border-color: #444; }
        
        /* Cell Status Styles */
        .cell-p { background-color: #FFC000; color: black; font-weight: bold; font-size: 7px; text-align: center; }
        .cell-e { background-color: #00B050; color: white; font-weight: bold; font-size: 7px; text-align: center; }
        .cell-nc { background-color: #FF0000; color: white; font-weight: bold; font-size: 7px; text-align: center; }
        .cell-empty { background-color: white; }
        
        /* Nested table for P/E split */
        .split-cell-table { width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0; }
        .split-cell-table td { border: none; padding: 0; width: 50%; height: 14px; line-height: 14px; text-align: center; }
        .border-right { border-right: 1px solid #ccc !important; }
    </style>
</head>
<body>
    <header>
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="https://i.ibb.co/XxdrPS6n/Logo-2.png" alt="ECYTEL" style="height: 50px; max-width: 100%;">
                </td>
                <td class="title-cell">
                    <h2 style="margin: 0; font-size: 16px;">{{ strtoupper($program->nombre ?? 'PROGRAMA ANUAL QHSE') }}</h2>
                    <h3 style="margin: 0; font-size: 12px; font-weight: normal;">{{ $program->anio }} - ECYTEL S.A.C.</h3>
                </td>
                <td class="info-cell">
                    <table style="width: 100%; border: none;">
                        <tr><td style="border:none;"><strong>Código:</strong> {{ $program->codigo ?? 'N/A' }}</td></tr>
                        <tr><td style="border:none;"><strong>Versión:</strong> {{ $program->version ?? '1.0' }}</td></tr>
                        <tr><td style="border:none;"><strong>Fecha:</strong> {{ $program->fecha_emision ?? date('d/m/Y') }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Página <span class="pagenum"></span>
    </footer>

    <div class="section-title">Objetivo General:</div>
    <div class="content-box">
        {{ $program->objetivo_general ?? $program->obj_general ?? 'Realizar el cumplimiento del Sistema Integrado de Gestión según las normativas vigentes...' }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" class="w-obj">OBJETIVOS ESPECIFICOS</th>
                <th rowspan="2" class="w-act">ACTIVIDADES POR PROGRAMA</th>
                <th rowspan="2" class="w-meta">META</th>
                <th rowspan="2" class="w-sede">SEDE</th>
                <th rowspan="2" class="w-resp">RESPONSABLE</th>
                <th rowspan="2" class="w-del">RESPONSABLE DELEGADO</th>
                <th rowspan="2" class="w-apo">APOYO</th>
                <th rowspan="2" class="w-freq">FRECUENCIA</th>
                <th colspan="12">CRONOGRAMA</th>
                <th rowspan="2" class="w-cump">% CUMP.</th>
                <th rowspan="2" class="w-obs">OBS.</th>
            </tr>
            <tr>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">ENE</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">FEB</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">MAR</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">ABR</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">MAY</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">JUN</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">JUL</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">AGO</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">SEP</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">OCT</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">NOV</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
                <th class="w-month" style="padding: 0; background-color: #003366;">
                    <div style="border-bottom: 1px solid #fff; padding: 2px 0;">DIC</div>
                    <table class="split-cell-table"><tr><td style="border-right: 1px solid #fff;">P</td><td>E</td></tr></table>
                </th>
            </tr>
        </thead>
        <tbody>
@php
    $totalProgramPlanned = 0;
    $totalProgramExecuted = 0;
@endphp

            @forelse($program->components as $component)
                @php
                    $componentPlanned = 0;
                    $componentExecuted = 0;
                    foreach($component->activities as $act) {
                        $componentPlanned += $act->veces_al_anio;
                        $componentExecuted += $act->executions->where('estado', \App\Enums\ActivityState::EJECUTADO)->count();
                    }
                    $componentPercent = $componentPlanned > 0 ? round(($componentExecuted / $componentPlanned) * 100) : 0;
                    
                    $totalProgramPlanned += $componentPlanned;
                    $totalProgramExecuted += $componentExecuted;
                @endphp

                <!-- Fila de Encabezado de Grupo (Componente) -->
                <tr class="group-header-row">
                    <!-- Título del Componente (Ocupa columnas de datos) -->
                    <td colspan="22" style="text-align: left; padding-left: 10px;">
                        {{ $loop->iteration }}.- {{ $component->name }}
                    </td>
                </tr>

                @if($component->activities->count() > 0)
                    @foreach($component->activities as $index => $activity)
                        @php
                            $actPlanned = $activity->veces_al_anio;
                            // Recalcular ejecuciones reales desde la relación para asegurar consistencia
                            $actExecuted = $activity->executions->where('estado', \App\Enums\ActivityState::EJECUTADO)->count();
                            $actPercent = $actPlanned > 0 ? round(($actExecuted / $actPlanned) * 100) : 0;
                        @endphp
                        <tr>
                            <!-- Objetivo Específico (Sin rowspan para evitar bugs de DomPDF) -->
                            @php
                                $isFirst = $index === 0;
                                $isLast = $index === ($component->activities->count() - 1);
                                $borderTop = $isFirst ? '1px solid #000' : 'none';
                                $borderBottom = $isLast ? '1px solid #000' : 'none';
                            @endphp
                            <td style="text-align: left; padding: 5px; font-size: 8px; border-top: <?php echo $borderTop; ?>; border-bottom: <?php echo $borderBottom; ?>; border-left: 1px solid #000; border-right: 1px solid #000;">
                                @if($isFirst)
                                    {{ $component->objetivo ?? 'Objetivo Específico del Componente ' . $loop->parent->iteration }}
                                @endif
                            </td>

                            <!-- Datos de la Actividad -->
                            <td style="text-align: left; padding-left: 5px;">
                                <strong>{{ $loop->parent->iteration }}.{{ $loop->iteration }}</strong> {{ $activity->nombre }}
                            </td>
                            <td>{{ $activity->meta }} {{ str_ireplace('Porcentaje', '%', $activity->unidad_medida) }}</td>
                            <td>{{ optional($activity->location)->nombre ?? '-' }}</td>
                            <td>{{ optional($activity->responsable)->nombre ?? '-' }}</td>
                            <td>{{ optional($activity->responsableDelegado)->nombre ?? '-' }}</td>
                            <td>{{ $activity->apoyo ?? '-' }}</td>
                            <td>{{ $activity->frecuencia }}</td>

                            <!-- Meses con Sub-casillas (P | E) -->
                            @php
                                // Lógica de Programación (P) basada en Frecuencia
                                $monthsList = [1,2,3,4,5,6,7,8,9,10,11,12];
                                $isScheduled = function($m) use ($activity) {
                                    $freq = strtolower($activity->frecuencia);
                                    if ($freq === 'mensual') return true;
                                    if ($freq === 'bimestral') return $m % 2 == 0;
                                    if ($freq === 'trimestral') return $m % 3 == 0;
                                    if ($freq === 'semestral') return $m % 6 == 0;
                                    if ($freq === 'anual') {
                                        $startMonth = $activity->fecha_inicio ? $activity->fecha_inicio->month : 1;
                                        return $m == $startMonth;
                                    }
                                    return false;
                                };
                            @endphp

                            @foreach($monthsList as $m)
                                @php
                                    $executionStatus = null;
                                    // Buscar ejecución para este mes
                                    $exec = $activity->executions->first(function($e) use ($m) {
                                        return $e->fecha_programada && $e->fecha_programada->month == $m;
                                    });
                                    
                                    if ($exec) {
                                        $executionStatus = $exec->estado;
                                    }
                                @endphp
                                <td style="padding: 0;">
                                    <table class="split-cell-table">
                                        <tr>
                                            <!-- Sub-casilla P (Programado) -->
                                            <td class="border-right {{ $isScheduled($m) ? 'cell-p' : '' }}">
                                                {{ $isScheduled($m) ? 'P' : '' }}
                                            </td>
                                            <!-- Sub-casilla E (Ejecutado) -->
                                            <td class="{{ $executionStatus === \App\Enums\ActivityState::EJECUTADO ? 'cell-e' : ($executionStatus === \App\Enums\ActivityState::NO_CUMPLIO ? 'cell-nc' : '') }}">
                                                @if($executionStatus === \App\Enums\ActivityState::EJECUTADO)
                                                    E
                                                @elseif($executionStatus === \App\Enums\ActivityState::NO_CUMPLIO)
                                                    NC
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            @endforeach

                            <!-- Cumplimiento Individual -->
                            <td>
                                @if($activity->veces_al_anio > 0)
                                    {{ round(($activity->ejecuciones_realizadas / $activity->veces_al_anio) * 100) }}%
                                @else
                                    0%
                                @endif
                            </td>
                            <td></td>
                        </tr>
                    @endforeach
                    
                    <!-- Fila de Porcentaje del Componente -->
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td colspan="20" style="text-align: right; padding-right: 10px;">% AVANCE POR SUBPROGRAMA</td>
                        <td colspan="2" style="text-align: center;">
                            @php
                                // Cálculo del promedio de los porcentajes de las actividades
                                $totalPercentage = 0;
                                $activityCount = $component->activities->count();
                                
                                foreach($component->activities as $act) {
                                    $actPlanned = $act->veces_al_anio;
                                    $actExecuted = $act->executions->where('estado', \App\Enums\ActivityState::EJECUTADO)->count();
                                    $actPercent = $actPlanned > 0 ? ($actExecuted / $actPlanned) * 100 : 0;
                                    $totalPercentage += $actPercent;
                                }
                                
                                $componentAveragePercent = $activityCount > 0 ? round($totalPercentage / $activityCount) : 0;
                            @endphp
                            {{ $componentAveragePercent }}%
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="22" style="text-align: center; color: #999;">Sin actividades registradas</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="22" style="text-align: center;">No hay componentes registrados.</td>
                </tr>
            @endforelse
            
            <!-- Fila Total del Programa -->
            @php
                // Cálculo del promedio total del programa (promedio de los promedios de componentes o promedio de todas las actividades)
                // Usaremos promedio de todas las actividades para mayor precisión general
                $totalProgramPercentageSum = 0;
                $totalActivitiesCount = 0;
                
                foreach($program->components as $component) {
                    foreach($component->activities as $act) {
                        $actPlanned = $act->veces_al_anio;
                        // Recalcular ejecuciones reales
                        $actExecuted = $act->executions->where('estado', \App\Enums\ActivityState::EJECUTADO)->count();
                        $actPercent = $actPlanned > 0 ? ($actExecuted / $actPlanned) * 100 : 0;
                        $totalProgramPercentageSum += $actPercent;
                        $totalActivitiesCount++;
                    }
                }
                
                $totalProgramPercent = $totalActivitiesCount > 0 ? round($totalProgramPercentageSum / $totalActivitiesCount) : 0;
            @endphp
            <tr style="background-color: #003366; color: white; font-weight: bold;">
                <td colspan="20" style="text-align: right; padding-right: 10px;">% AVANCE TOTAL DEL PROGRAMA</td>
                <td colspan="2" style="text-align: center;">{{ $totalProgramPercent }}%</td>
            </tr>
        </tbody>
    </table>
</html>
