<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Programa</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $record->activity->program->nombre ?? '-' }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Actividad</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $record->activity->nombre ?? '-' }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Fecha Programada</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $record->fecha_programada->format('d/m/Y') }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Estado</h3>
            <span @class([
                'px-2 py-1 text-xs font-medium rounded-full',
                'bg-green-100 text-green-800' => $record->estado->value === 'ejecutado',
                'bg-yellow-100 text-yellow-800' => $record->estado->value === 'programado',
                'bg-blue-100 text-blue-800' => $record->estado->value === 'en_proceso',
                'bg-red-100 text-red-800' => $record->estado->value === 'no_cumplio',
                'bg-gray-100 text-gray-800' => !in_array($record->estado->value, ['ejecutado', 'programado', 'en_proceso', 'no_cumplio']),
            ])>
                {{ $record->estado->getLabel() }}
            </span>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Ubicación</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $record->activity->location->nombre ?? '-' }}</p>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Responsable</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $record->activity->responsable->nombre ?? '-' }}</p>
        </div>
    </div>

    @if($record->observacion)
        <div class="mt-4">
            <h3 class="text-sm font-medium text-gray-500">Observación</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $record->observacion }}</p>
        </div>
    @endif

    @if($record->fecha_ejecucion_real)
        <div class="mt-4 border-t pt-4">
            <h3 class="text-sm font-medium text-gray-500">Fecha de Ejecución Real</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $record->fecha_ejecucion_real->format('d/m/Y') }}</p>
        </div>
    @endif
</div>
