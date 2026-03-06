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
            <div class="flex flex-col gap-2 mb-2">
                <nav class="flex items-center text-sm text-gray-500 dark:text-gray-400 overflow-x-auto whitespace-nowrap pb-2">
                    <button wire:click="resetNavigation" class="hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-1 transition-colors">
                        <x-heroicon-o-home class="w-4 h-4" />
                        <span>Inicio</span>
                    </button>
                    
                    @foreach($this->breadcrumbs as $crumb)
                        <x-heroicon-m-chevron-right class="w-4 h-4 mx-2 text-gray-400 flex-shrink-0" />
                        <span class="max-w-[150px] truncate cursor-default" title="{{ $crumb['name'] }}">
                            {{ $crumb['name'] }}
                        </span>
                    @endforeach

                    <x-heroicon-m-chevron-right class="w-4 h-4 mx-2 text-gray-400 flex-shrink-0" />
                    <span class="font-medium text-gray-900 dark:text-white max-w-[200px] truncate" title="{{ $this->activeFolderName }}">
                        {{ $this->activeFolderName }}
                    </span>
                </nav>

                <button wire:click="goBack" class="self-start hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-1 transition-colors text-sm font-medium">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Volver
                </button>
            </div>
        @endif

        @if($isRoot || count($folders) > 0)
            <!-- Folders Grid -->
            <div>
                @if(!$isRoot)
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <x-heroicon-o-folder class="w-5 h-5 text-gray-400" />
                        Subcarpetas
                    </h2>
                @else
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <x-heroicon-o-folder class="w-5 h-5 text-gray-400" />
                        Carpetas
                    </h2>
                @endif
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($folders as $folder)
                        <div 
                            wire:click="openFolder('{{ $folder['id'] }}', '{{ $folder['name'] }}', '{{ $folder['type'] }}')"
                            class="group cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 hover:shadow-md transition-all duration-200 flex flex-col items-center justify-center gap-3 text-center"
                        >
                            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-full group-hover:scale-110 transition-transform duration-200">
                                @if($folder['type'] === 'activity')
                                    <x-heroicon-s-document-duplicate class="w-8 h-8 text-blue-500" />
                                @else
                                    <x-heroicon-s-folder class="w-8 h-8 text-amber-500" />
                                @endif
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ $folder['name'] }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $folder['count'] }} elementos
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($isRoot && count($recentFiles) > 0)
            <!-- Recent Files -->
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
                                            <button 
                                                wire:click="selectFile('{{ $file['path'] }}')"
                                                class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium text-xs hover:underline cursor-pointer"
                                            >
                                                Ver archivo
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if(!$isRoot && isset($files))
            <!-- Files List (Folder Content) -->
            <div class="mt-4">
                 <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-heroicon-o-document-duplicate class="w-5 h-5 text-gray-400" />
                    Archivos
                </h2>
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
                                            <button 
                                                wire:click="selectFile('{{ $file['path'] }}')"
                                                class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium text-xs hover:underline cursor-pointer"
                                            >
                                                <x-heroicon-o-eye class="w-3 h-3" />
                                                Ver
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        @if(count($folders) === 0)
                        <div class="p-12 text-center">
                            <x-heroicon-o-folder-open class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Carpeta vacía</h3>
                            <p class="text-gray-500 dark:text-gray-400 mt-1">No hay archivos ni carpetas en esta ubicación.</p>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif

    </div>

    {{-- File Preview Modal --}}
    @if($this->uploadedFileUrl)
        @teleport('body')
        <div 
            x-data
            x-init="$nextTick(() => { document.body.style.overflow = 'hidden' }); $watch('$wire.uploadedFileUrl', value => { document.body.style.overflow = value ? 'hidden' : 'auto' })"
            class="fixed inset-0 z-[99999] w-screen h-screen bg-gray-900/90 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
            style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999;"
        >
            <!-- Modal Container -->
            <div 
                class="relative w-full max-w-6xl h-[90vh] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl flex flex-col overflow-hidden ring-1 ring-white/10"
                @click.outside="$wire.set('uploadedFileUrl', null)"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 z-10">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
                            <x-heroicon-o-document-text class="w-5 h-5 text-gray-600 dark:text-gray-300" />
                        </div>
                        <div class="flex flex-col min-w-0">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate max-w-xl">
                                {{ basename($this->uploadedFilePath) }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Vista previa del documento
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <a 
                            href="{{ route('google-drive.download', ['path' => $this->uploadedFilePath]) }}" 
                            class="p-2 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                            title="Descargar"
                        >
                            <x-heroicon-m-arrow-down-tray class="w-5 h-5" />
                        </a>
                        <button 
                            wire:click="$set('uploadedFileUrl', null)"
                            class="p-2 text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                            title="Cerrar (Esc)"
                        >
                            <x-heroicon-m-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Modal Content (Iframe) -->
                <div class="flex-1 bg-gray-100 dark:bg-gray-950 relative w-full h-full">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <x-filament::loading-indicator class="h-8 w-8 text-gray-400" />
                    </div>
                    @php
                        $mime = 'application/octet-stream';
                        try {
                             $ext = strtolower(pathinfo($this->uploadedFilePath, PATHINFO_EXTENSION));
                             if ($ext === 'pdf') $mime = 'application/pdf';
                             elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $mime = 'image/' . $ext;
                        } catch (\Exception $e) {}
                    @endphp

                    @if($mime === 'application/pdf' || str_contains($mime, 'image'))
                        <iframe 
                            src="{{ $this->uploadedFileUrl }}" 
                            class="absolute inset-0 w-full h-full z-10" 
                            frameborder="0"
                            allowfullscreen
                        ></iframe>
                    @else
                         <!-- Fallback for non-previewable files -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center z-10 p-6 text-center">
                            <x-heroicon-o-document class="w-16 h-16 text-gray-400 mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Vista previa no disponible</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mt-2">
                                Este tipo de archivo no se puede previsualizar directamente en el navegador. Por favor, descárgalo para verlo.
                            </p>
                            <a 
                                href="{{ route('google-drive.download', ['path' => $this->uploadedFilePath]) }}" 
                                class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors"
                            >
                                <x-heroicon-m-arrow-down-tray class="w-5 h-5" />
                                Descargar archivo
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endteleport
    @endif
</x-filament-panels::page>
