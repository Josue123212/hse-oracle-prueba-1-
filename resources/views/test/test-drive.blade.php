<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Main Content Card -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">
                        Evidencias
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Gestión de archivos almacenados en Google Drive
                    </p>
                </div>
                <x-filament::button
                    size="sm"
                    color="gray"
                    wire:click="refreshFilesList"
                    icon="heroicon-m-arrow-path"
                    class="shadow-sm"
                >
                    Actualizar
                </x-filament::button>
            </div>

            <div class="p-6">
                <!-- Section: Existing Files -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                        <x-heroicon-m-folder-open class="w-4 h-4 text-gray-400" style="width: 1rem; height: 1rem;"/>
                        Archivos Disponibles
                    </h3>

                    @if(count($filesList) > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($filesList as $file)
                                <div 
                                    wire:key="{{ $file['path'] }}"
                                    class="group relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 transition-all duration-200 hover:shadow-md hover:border-primary-500/50 dark:hover:border-primary-400/50"
                                >
                                    <!-- File Icon & Actions -->
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600 dark:text-blue-400">
                                            @if(str_contains($file['mime'], 'image'))
                                                <x-heroicon-o-photo class="w-6 h-6" style="width: 1.5rem; height: 1.5rem;" />
                                            @elseif(str_contains($file['mime'], 'pdf'))
                                                <x-heroicon-o-document-text class="w-6 h-6" style="width: 1.5rem; height: 1.5rem;" />
                                            @else
                                                <x-heroicon-o-document class="w-6 h-6" style="width: 1.5rem; height: 1.5rem;" />
                                            @endif
                                        </div>
                                        
                                        <!-- Quick Action Buttons (Hover) -->
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 relative z-20 transition-all transform scale-90 group-hover:scale-100">
                                            <a 
                                                href="{{ route('google-drive.download', ['path' => $file['path']]) }}"
                                                class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
                                                title="Descargar archivo"
                                            >
                                                <x-heroicon-m-arrow-down-tray class="w-4 h-4" style="width: 1rem; height: 1rem;" />
                                            </a>
                                            
                                            <button 
                                                type="button"
                                                wire:click.stop="deleteFile('{{ $file['path'] }}')"
                                                wire:confirm="¿Estás seguro de que deseas eliminar este archivo? Esta acción no se puede deshacer."
                                                class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                                                title="Eliminar archivo"
                                            >
                                                <x-heroicon-m-trash class="w-4 h-4" style="width: 1rem; height: 1rem;" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- File Info -->
                                    <div class="space-y-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate" title="{{ $file['name'] }}">
                                            {{ $file['name'] }}
                                        </h4>
                                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ number_format($file['size'] / 1024, 1) }} KB</span>
                                            <span>&bull;</span>
                                            <span class="uppercase">{{ pathinfo($file['name'], PATHINFO_EXTENSION) }}</span>
                                        </div>
                                    </div>

                                    <!-- Full Card Click Area -->
                                    <button 
                                        wire:click="selectFile(@js($file['path']))"
                                        class="absolute inset-0 w-full h-full rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-800 z-10"
                                    >
                                        <span class="sr-only">Ver {{ $file['name'] }}</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 p-12 text-center bg-gray-50 dark:bg-gray-900/50">
                            <x-heroicon-o-document-magnifying-glass class="mx-auto h-12 w-12 text-gray-400" style="width: 3rem; height: 3rem; margin: 0 auto;" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay archivos</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comienza subiendo un nuevo documento.</p>
                        </div>
                    @endif
                </div>

                <!-- Section: Upload -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                        <x-heroicon-m-arrow-up-tray class="w-4 h-4 text-gray-400" style="width: 1rem; height: 1rem;" />
                        Subir Nueva Evidencia
                    </h3>
                    <div class="bg-gray-50 dark:bg-gray-900/30 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-1">
                        {{ $this->uploadAction }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Screen Modal Overlay (Teleported to body) -->
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
                            <x-heroicon-o-document-text class="w-5 h-5 text-gray-600 dark:text-gray-300" style="width: 1.25rem; height: 1.25rem;" />
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
                            href="{{ $this->uploadedFileUrl }}" 
                            target="_blank"
                            class="p-2 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                            title="Abrir en nueva pestaña"
                        >
                            <x-heroicon-m-arrow-top-right-on-square class="w-5 h-5" style="width: 1.25rem; height: 1.25rem;" />
                        </a>
                        <button 
                            wire:click="$set('uploadedFileUrl', null)"
                            class="p-2 text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                            title="Cerrar (Esc)"
                        >
                            <x-heroicon-m-x-mark class="w-6 h-6" style="width: 1.5rem; height: 1.5rem;" />
                        </button>
                    </div>
                </div>

                <!-- Modal Content (Iframe) -->
                <div class="flex-1 bg-gray-100 dark:bg-gray-950 relative w-full h-full">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <x-filament::loading-indicator class="h-8 w-8 text-gray-400" />
                    </div>
                    <iframe 
                        src="{{ $this->uploadedFileUrl }}" 
                        class="absolute inset-0 w-full h-full z-10" 
                        frameborder="0"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    <!-- Loading State Indicator (Global) -->
    @teleport('body')
    <div 
        wire:loading 
        wire:target="selectFile" 
        class="fixed inset-0 z-[110] flex items-center justify-center bg-black/20 backdrop-blur-[2px] cursor-wait"
        style="z-index: 110;"
    >
        <div class="bg-white dark:bg-gray-800 px-6 py-4 rounded-xl shadow-2xl flex items-center gap-4 border border-gray-100 dark:border-gray-700 transform scale-100 animate-in fade-in zoom-in duration-200">
            <x-filament::loading-indicator class="h-6 w-6 text-primary-600 dark:text-primary-400" />
            <div class="flex flex-col">
                <span class="text-sm font-semibold text-gray-900 dark:text-white">Cargando documento</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Por favor espere...</span>
            </div>
        </div>
    </div>
    @endteleport

</x-filament-panels::page>