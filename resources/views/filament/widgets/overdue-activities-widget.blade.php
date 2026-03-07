<x-filament-widgets::widget id="overdue-activities-widget">
    {{-- Script de apertura automática eliminado a petición: solo scroll requerido --}}
    
    {{-- Trigger notification check removed from here --}}

    <div class="flex items-center justify-between gap-x-3 mb-4">
        <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white sm:text-xl">
            Ejecuciones Vencidas (Urgente)
        </h2>
        
        @if(!$this->showAllExecutions)
            <x-filament::button 
                wire:click="toggleView"
                color="danger"
                icon="heroicon-o-exclamation-triangle"
            >
                Ver todas las urgentes
            </x-filament::button>
        @else
            <x-filament::button 
                wire:click="toggleView"
                color="gray"
                icon="heroicon-o-arrow-left"
            >
                Volver
            </x-filament::button>
        @endif
    </div>

    @if(!$this->showAllExecutions)
        {{-- Seccion de Actividades Criticas --}}
        @if($this->criticalActivities->isNotEmpty())
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wider flex items-center gap-2">
                    <x-heroicon-m-fire class="w-4 h-4 text-red-500" />
                    Actividades Más Críticas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($this->criticalActivities as $execution)
                            <div 
                                wire:click="mountAction('viewActivity', { record: {{ $execution->id }} })"
                                class="relative group bg-white dark:bg-gray-900 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 hover:shadow-md transition-all duration-300 cursor-pointer flex flex-col"
                                style="min-height: 200px;"
                            >
                                <div class="absolute top-0 left-0 w-1 h-full bg-red-500 rounded-l-xl"></div>
                                
                                <div class="flex flex-col h-full pl-3">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-900/20 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-600/10">
                                            {{ ucfirst($execution->activity->tipo) }}
                                        </span>
                                        <div class="text-xs text-gray-400 font-mono">
                                            {{ $execution->fecha_programada->diffForHumans() }}
                                        </div>
                                    </div>

                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 mb-2" title="{{ $execution->activity->nombre }}">
                                        {{ $execution->activity->nombre }}
                                    </h4>

                                    <div class="mt-auto pt-2 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2 text-xs">
                                        <div class="flex flex-col gap-1 min-w-0">
                                            <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                                <x-heroicon-m-calendar class="w-3 h-3 flex-shrink-0" />
                                                <span class="truncate">{{ $execution->fecha_programada->format('d/m/Y') }}</span>
                                            </div>
                                            @if($execution->activity->responsable)
                                                <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400 truncate" title="{{ $execution->activity->responsable->nombre }}">
                                                    <x-heroicon-m-user class="w-3 h-3 flex-shrink-0" />
                                                    <span class="truncate">{{ $execution->activity->responsable->nombre }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="flex-shrink-0" wire:click.stop>
                                            {{ ($this->regularizeActivityAction)(['record' => $execution->id]) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
        @endif
    @else
        <div class="overdue-activities-list-wrapper relative mt-4 w-full border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm bg-white dark:bg-gray-900" style="max-height: 500px; overflow-y: auto;">
            <div class="flex flex-col divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($this->overdueActivities as $execution)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                        {{-- Fila 1: Nombre de la Actividad --}}
                        <div class="mb-3">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white" title="{{ $execution->activity->nombre }}">
                                {{ $execution->activity->nombre }}
                            </h4>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-900/20 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-600/10">
                                    {{ ucfirst($execution->activity->tipo) }}
                                </span>
                            </div>
                        </div>

                        {{-- Fila 2: Botones y Fecha --}}
                        <div class="flex flex-wrap items-center gap-3">
                            {{-- Botón Regularizar --}}
                            <div class="flex-shrink-0">
                                <x-filament::button
                                    color="danger"
                                    size="xs"
                                    icon="heroicon-o-play"
                                    wire:click="mountAction('regularizeActivity', { record: '{{ $execution->id }}' })"
                                >
                                    Regularizar
                                </x-filament::button>
                            </div>

                            {{-- Botón Ver (al lado de Regularizar) --}}
                            <div class="flex-shrink-0">
                                <x-filament::button
                                    color="gray"
                                    size="xs"
                                    icon="heroicon-o-eye"
                                    wire:click="mountAction('viewActivity', { record: '{{ $execution->id }}' })"
                                >
                                    Ver
                                </x-filament::button>
                            </div>

                            {{-- Fecha Programada --}}
                            <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 ml-auto">
                                <x-heroicon-m-calendar class="w-4 h-4 flex-shrink-0 text-gray-400" />
                                <span class="font-medium">Prog: {{ $execution->fecha_programada->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($this->overdueActivities->isEmpty())
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No hay ejecuciones vencidas adicionales.
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <x-filament-actions::modals />
</x-filament-widgets::widget>