<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Inspección #{{ $inspection->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 14px; color: #555; }
        .section { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Inspección</div>
        <div class="subtitle">ID: {{ $inspection->id }} - Fecha Programada: {{ $inspection->fecha_programada }}</div>
    </div>

    <table class="table">
        <tr>
            <th>Tipo de Inspección</th>
            <td>{{ optional($inspection->inspectionType)->nombre }}</td>
            <th>Actividad</th>
            <td>{{ optional($inspection->activity)->nombre }}</td>
        </tr>
        <tr>
            <th>Sede / Ubicación</th>
            <td>{{ optional($inspection->location)->nombre }}</td>
            <th>Inspector</th>
            <td>{{ optional($inspection->user)->name }}</td>
        </tr>
        <tr>
            <th>Estado</th>
            <td colspan="3">{{ ucfirst($inspection->estado) }}</td>
        </tr>
    </table>

    <div class="section">
        <strong>Observaciones:</strong>
        <p>{{ $inspection->observaciones ?? 'Sin observaciones registradas.' }}</p>
    </div>

</body>
</html>
