<x-filament-panels::page>
    <div class="space-y-4 max-w-7xl mx-auto">
        <!-- View Mode Switcher -->
        <div class="flex justify-end mb-4">
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                    type="button" 
                    wire:click="$set('viewMode', 'program')"
                    class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-2 focus:ring-primary-700 focus:text-primary-700 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-primary-500 dark:focus:text-white {{ $viewMode === 'program' ? 'bg-gray-100 text-primary-700 dark:bg-gray-700' : 'bg-white text-gray-900 dark:bg-gray-800' }}"
                >
                    Por Programa
                </button>
                <button 
                    type="button" 
                    wire:click="$set('viewMode', 'type')"
                    class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-r-lg hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-2 focus:ring-primary-700 focus:text-primary-700 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-primary-500 dark:focus:text-white {{ $viewMode === 'type' ? 'bg-gray-100 text-primary-700 dark:bg-gray-700' : 'bg-white text-gray-900 dark:bg-gray-800' }}"
                >
                    Por Tipo de Actividad
                </button>
            </div>
        </div>

        @if($viewMode === 'program')
            @foreach ($programs as $program)
                @include('filament.pages.repository.partials.program-item', ['program' => $program])
            @endforeach
        @else
            @foreach ($activitiesByType as $type => $activities)
                <div 
                    x-data="{ open: false }" 
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-300 hover:shadow-md"
                >
                    <!-- Type Header -->
                    <div 
                        @click="open = !open" 
                        class="px-5 py-4 flex items-center justify-between cursor-pointer bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
                    >
                        <div class="flex items-center gap-4">
                            <div class="shrink-0 w-10 h-10 flex items-center justify-center p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                                <x-heroicon-o-tag class="w-6 h-6 text-indigo-600 dark:text-indigo-400" style="width: 1.5rem; height: 1.5rem;" />
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 capitalize">
                                    {{ str_replace('_', ' ', $type) }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $activities->count() }} actividades registradas
                                </p>
                            </div>
                        </div>
                        
                        <!-- Chevron Container to force size -->
                        <div class="shrink-0 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <x-heroicon-o-chevron-down 
                                class="w-5 h-5 text-gray-400 transition-transform duration-300"
                                x-bind:class="{ 'rotate-180': open }"
                                style="width: 1.25rem; height: 1.25rem;"
                            />
                        </div>
                    </div>

                    <!-- Type Content (Activities) -->
                    <div 
                        x-show="open" 
                        x-collapse
                        class="border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/30 dark:bg-gray-900/30"
                    >
                        <div class="p-4 sm:p-6 space-y-4">
                            @foreach ($activities as $activity)
                                @include('filament.pages.repository.partials.activity-item', ['activity' => $activity])
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</x-filament-panels::page>
