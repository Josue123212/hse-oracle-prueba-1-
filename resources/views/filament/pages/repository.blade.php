<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <!-- Toolbar: Search & View Mode -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white dark:bg-gray-900 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            
            <!-- Search -->
            <div class="w-full sm:max-w-md relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.500ms="search"
                    placeholder="Buscar carpetas o archivos..." 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition duration-150 ease-in-out"
                >
            </div>

            <!-- View Mode Switcher -->
            @if($isRoot)
            <div class="flex bg-gray-100 dark:bg-gray-800 p-1 rounded-lg">
                <button 
                    wire:click="$set('viewMode', 'program')"
                    class="px-4 py-1.5 text-sm font-medium rounded-md transition-all duration-200 {{ $viewMode === 'program' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}"
                >
                    Programas
                </button>
                <button 
                    wire:click="$set('viewMode', 'type')"
                    class="px-4 py-1.5 text-sm font-medium rounded-md transition-all duration-200 {{ $viewMode === 'type' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}"
                >
                    Tipos
                </button>
            </div>
            @endif
        </div>

        @if(!$isRoot)
            <!-- Breadcrumb / Back Navigation -->
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <button wire:click="goBack" class="hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-1 transition-colors">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Volver
                </button>
                <span class="text-gray-300">/</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $folderName }}</span>
            </div>
        @endif

        @if($isRoot)
            <!-- Folders Grid -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-heroicon-o-folder class="w-5 h-5 text-gray-400" />
                    Carpetas
                </h2>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($folders as $folder)
                        <div 
                            wire:click="openFolder('{{ $folder['id'] }}', '{{ $folder['name'] }}')"
                            class="group cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 hover:shadow-md transition-all duration-200 flex flex-col items-center justify-center gap-3 text-center"
                        >
                            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-full group-hover:scale-110 transition-transform duration-200">
                                <x-heroicon-s-folder class="w-8 h-8 text-amber-500" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ $folder['name'] }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $folder['count'] }} archivos
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Files -->
            @if(count($recentFiles) > 0)
            <div class="mt-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-heroicon-o-clock class="w-5 h-5 text-gray-400" />
                    Recientes
                </h2>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3 font-medium">Nombre</th>
                                    <th class="px-6 py-3 font-medium">Fecha</th>
                                    <th class="px-6 py-3 font-medium text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($recentFiles as $file)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <x-heroicon-o-document-text class="w-5 h-5 text-gray-400" />
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $file['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                            {{ $file['date'] }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a 
                                                href="{{ $file['url'] }}" 
                                                target="_blank"
                                                class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium text-xs hover:underline"
                                            >
                                                Ver archivo
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        @else
            <!-- Files List (Folder Content) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                @if(count($files) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 font-medium">Nombre</th>
                                <th class="px-6 py-3 font-medium">Actividad</th>
                                <th class="px-6 py-3 font-medium">Fecha</th>
                                <th class="px-6 py-3 font-medium">Tipo</th>
                                <th class="px-6 py-3 font-medium text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($files as $file)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($file['type'] === 'PDF')
                                                <x-heroicon-o-document-text class="w-5 h-5 text-red-500" />
                                            @elseif(in_array($file['type'], ['JPG', 'PNG', 'JPEG']))
                                                <x-heroicon-o-photo class="w-5 h-5 text-blue-500" />
                                            @else
                                                <x-heroicon-o-document class="w-5 h-5 text-gray-400" />
                                            @endif
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $file['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $file['activity_name'] }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $file['date'] }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700">
                                            {{ $file['type'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a 
                                            href="{{ $file['url'] }}" 
                                            target="_blank"
                                            class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium text-xs hover:underline"
                                        >
                                            <x-heroicon-o-eye class="w-3 h-3" />
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <div class="p-12 text-center">
                        <x-heroicon-o-folder-open class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Carpeta vacía</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">No hay archivos en esta ubicación.</p>
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-filament-panels::page>
