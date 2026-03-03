<table>
    <thead>
        <tr>
            <th colspan="35"></th>
        </tr>
        <tr>
            <th colspan="33" style="text-align: center; font-weight: bold; font-size: 16px;">{{ strtoupper($program->nombre ?? 'PROGRAMA ANUAL QHSE') }} - {{ $program->anio }} - ECYTEL S.A.C.</th>
            <th style="font-weight: bold; text-align: right;">Código</th>
            <th style="text-align: left;">QHSE-QHSE-PROG-01</th>
        </tr>
        <tr>
            <th colspan="33"></th>
            <th style="font-weight: bold; text-align: right;">Versión</th>
            <th style="text-align: left;">06</th>
        </tr>
        <tr>
            <th colspan="35"></th>
        </tr>
        <tr>
            <th colspan="33"></th>
            <th style="font-weight: bold; text-align: right;">Fecha</th>
            <th style="text-align: left;">{{ date('d/m/Y') }}</th>
        </tr>
        <tr>
            <th colspan="35" style="font-weight: bold; font-size: 14px;">Objetivo General:</th>
        </tr>
        <tr>
            <td colspan="35" style="height: 60px; vertical-align: top; text-align: left; wrap-text: true;">
                {{ $program->objetivo_general ?? $program->obj_general ?? 'Realizar el cumplimiento del Sistema Integrado de Gestión según las normativas vigentes y los estándares de los clientes relacionados en Seguridad, Salud y Medio Ambiente.' }}
            </td>
        </tr>
        <tr>
            <td colspan="35"></td>
        </tr>
        <tr>
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; text-align: center; background-color: #003366; color: white; border: 1px solid #000000; width: 200px;">OBJETIVOS ESPECIFICOS</th>
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; background-color: #003366; color: white; border: 1px solid #000000; width: 50px;">ITEM</th>
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; background-color: #003366; color: white; border: 1px solid #000000; width: 300px;">ACTIVIDADES POR PROGRAMA</th>
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; background-color: #003366; color: white; border: 1px solid #000000;">META</th>
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; background-color: #003366; color: white; border: 1px solid #000000;">SEDE</th>
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; background-color: #003366; color: white; border: 1px solid #000000;">RESPONSABLE</th>
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; background-color: #003366; color: white; border: 1px solid #000000;">RESPONSABLE DELEGADO</th>
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; background-color: #003366; color: white; border: 1px solid #000000;">APOYO</th>
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; background-color: #003366; color: white; border: 1px solid #000000;">FRECUENCIA</th>
            @foreach(['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'] as $month)
                <th colspan="2" style="font-weight: bold; text-align: center; background-color: #003366; color: white; border: 1px solid #000000;">{{ $month }}</th>
            @endforeach
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; background-color: #003366; color: white; border: 1px solid #000000;">% CUMP.</th>
            <th rowspan="2" style="font-weight: bold; vertical-align: middle; background-color: #003366; color: white; border: 1px solid #000000;">OBS.</th>
        </tr>
        <tr>
            <!-- Sub-headers for P/E are actually in the next row in Excel logic if we use rowspan=2 above. 
                 But wait, if rowspan=2, the next row definition should only contain the columns that are NOT rowspan=2.
                 So here we define the P/E columns. -->
            @for($i=0; $i<12; $i++)
                <th style="background-color: #cccccc; text-align: center; border: 1px solid #000000; width: 30px;">P</th>
                <th style="background-color: #cccccc; text-align: center; border: 1px solid #000000; width: 30px;">E</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach($program->components as $componentIndex => $component)
            <tr>
                <td colspan="35" style="font-weight: bold; background-color: #f0f0f0; border: 1px solid #000000;">{{ $loop->iteration }}.- {{ $component->name }}</td>
            </tr>
            @foreach($component->activities as $activityIndex => $activity)
                <tr>
                    <td style="vertical-align: top; text-align: left; border: 1px solid #000000;">
                        @if($activityIndex === 0)
                            {{ preg_replace('/\s*-\s*Prog\s*\d+/i', '', $component->objetivo ?? 'Objetivo Específico') }}
                        @endif
                    </td>
                    <td style="text-align: center; border: 1px solid #000000;">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                    <td style="text-align: left; border: 1px solid #000000;">{{ $activity->nombre }}</td>
                    <td style="text-align: center; border: 1px solid #000000;">{{ $activity->meta }} {{ str_ireplace('Porcentaje', '%', $activity->unidad_medida) }}</td>
                    <td style="text-align: center; border: 1px solid #000000;">{{ optional($activity->location)->nombre ?? '-' }}</td>
                    <td style="text-align: center; border: 1px solid #000000;">{{ optional($activity->responsable)->nombre ?? '-' }}</td>
                    <td style="text-align: center; border: 1px solid #000000;">{{ optional($activity->responsableDelegado)->nombre ?? '-' }}</td>
                    <td style="text-align: center; border: 1px solid #000000;">{{ $activity->apoyo ?? '-' }}</td>
                    <td style="text-align: center; border: 1px solid #000000;">{{ $activity->frecuencia }}</td>

                    @php
                        $monthsList = range(1, 12);
                        // Frequency Logic (Reused)
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
                            $exec = $activity->executions->first(function($e) use ($m) {
                                return $e->fecha_programada && $e->fecha_programada->month == $m;
                            });
                            $executionStatus = $exec ? $exec->estado : null;
                            
                            $pStyle = $isScheduled($m) ? 'background-color: #FFC000; text-align: center; border: 1px solid #000000;' : 'text-align: center; border: 1px solid #000000;';
                            $pContent = $isScheduled($m) ? 'P' : '';

                            $eStyle = 'text-align: center; border: 1px solid #000000;';
                            $eContent = '';
                            if ($executionStatus === \App\Enums\ActivityState::EJECUTADO) {
                                $eStyle = 'background-color: #00B050; color: white; text-align: center; border: 1px solid #000000;';
                                $eContent = 'E';
                            } elseif ($executionStatus === \App\Enums\ActivityState::NO_CUMPLIO) {
                                $eStyle = 'background-color: #FF0000; color: white; text-align: center; border: 1px solid #000000;';
                                $eContent = 'NC';
                            }
                        @endphp
                        <td style="{{ $pStyle }}">{{ $pContent }}</td>
                        <td style="{{ $eStyle }}">{{ $eContent }}</td>
                    @endforeach
                    
                    <td style="text-align: center; border: 1px solid #000000;">
                        <!-- Placeholder for formula or calculation -->
                        0%
                    </td>
                    <td style="border: 1px solid #000000;"></td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
