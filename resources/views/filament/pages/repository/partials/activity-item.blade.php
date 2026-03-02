<div 
    x-data="{ openActivity: false }"
    class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden"
>
    <!-- Activity Header -->
    <div 
        @click="openActivity = !openActivity"
        class="px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
    >
        <div class="flex items-center gap-3">
            <div class="shrink-0 w-6 h-6 flex items-center justify-center">
                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors" style="width: 1.25rem; height: 1.25rem;" />
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ $activity->nombre }}
            </span>
        </div>
        <div class="shrink-0 w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <x-heroicon-o-chevron-right 
                class="w-4 h-4 text-gray-400 transition-transform duration-200"
                x-bind:class="{ 'rotate-90': openActivity }"
                style="width: 1rem; height: 1rem;"
            />
        </div>
    </div>

    <!-- Activity Content (Executions) -->
    <div 
        x-show="openActivity" 
        x-collapse
        class="bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/50"
    >
        <div class="p-4">
            @if($activity->executions->isEmpty())
                <p class="text-xs text-gray-500 italic pl-2">No hay ejecuciones registradas.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach ($activity->executions as $execution)
                        <div class="group relative flex flex-col p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                            <div class="flex items-start justify-between mb-3">
                                <div class="shrink-0 w-10 h-10 flex items-center justify-center p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                    <x-heroicon-o-document-text class="w-6 h-6 text-gray-500 dark:text-gray-400" style="width: 1.5rem; height: 1.5rem;" />
                                </div>
                                <span class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wider rounded-full 
                                    {{ match($execution->estado->name ?? '') {
                                        'PROGRAMADO' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        'EJECUTADO' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'CANCELADO' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                    } }}">
                                    {{ $execution->estado->name ?? 'N/A' }}
                                </span>
                            </div>
                            
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1 line-clamp-2 min-h-[2.5rem]">
                                {{ $activity->nombre }}
                            </h4>
                            
                            <div class="mt-auto pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Fecha Programada<br>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($execution->fecha_programada)->format('d M, Y') }}
                                    </span>
                                </span>
                                
                                {{ ($this->viewDocumentAction)(['execution_id' => $execution->id]) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
