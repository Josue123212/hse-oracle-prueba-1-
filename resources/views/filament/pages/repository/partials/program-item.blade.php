<div 
    x-data="{ open: false }" 
    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-300 hover:shadow-md"
>
    <!-- Program Header -->
    <div 
        @click="open = !open" 
        class="px-5 py-4 flex items-center justify-between cursor-pointer bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
    >
        <div class="flex items-center gap-4">
            <div class="shrink-0 w-10 h-10 flex items-center justify-center p-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                <x-heroicon-o-folder class="w-6 h-6 text-primary-600 dark:text-primary-400" style="width: 1.5rem; height: 1.5rem;" />
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ $program->nombre }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $program->activities->count() }} actividades / {{ $program->children->count() }} sub-programas
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

    <!-- Program Content (Activities & Children) -->
    <div 
        x-show="open" 
        x-collapse
        class="border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/30 dark:bg-gray-900/30"
    >
        <div class="p-4 sm:p-6 space-y-4">
            <!-- Sub-Programs (Children) -->
            @if($program->children->isNotEmpty())
                <div class="space-y-4 pl-4 border-l-2 border-primary-100 dark:border-primary-900/50">
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Sub-Programas</h4>
                    @foreach ($program->children as $childProgram)
                        @include('filament.pages.repository.partials.program-item', ['program' => $childProgram])
                    @endforeach
                </div>
            @endif

            <!-- Activities -->
            @if($program->activities->isNotEmpty())
                <div class="space-y-4">
                    @if($program->children->isNotEmpty())
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2 mt-4">Actividades</h4>
                    @endif
                    
                    @foreach ($program->activities as $activity)
                        @include('filament.pages.repository.partials.activity-item', ['activity' => $activity])
                    @endforeach
                </div>
            @endif

            @if($program->activities->isEmpty() && $program->children->isEmpty())
                <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-dashed border-gray-200 dark:border-gray-700">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-gray-400" />
                    <p class="text-sm text-gray-500">No hay contenido registrado en este programa.</p>
                </div>
            @endif
        </div>
    </div>
</div>
