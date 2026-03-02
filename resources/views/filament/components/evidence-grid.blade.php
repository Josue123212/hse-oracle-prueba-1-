@props(['files' => [], 'mode' => 'view', 'recordId' => null])

<div class="max-h-[300px] overflow-y-auto p-1 space-y-2">
    @foreach($files as $file)
        <div 
            wire:key="{{ $file['path'] }}"
            class="group relative flex items-center gap-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 transition-all duration-200 hover:shadow-md hover:border-primary-500/50 dark:hover:border-primary-400/50"
        >
            <!-- File Icon -->
            <div class="flex-shrink-0 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600 dark:text-blue-400">
                @php
                    $mime = $file['mime'] ?? 'application/octet-stream';
                    $extension = pathinfo($file['path'], PATHINFO_EXTENSION);
                @endphp
                
                @if(str_contains($mime, 'image') || in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                    <x-heroicon-o-photo class="w-8 h-8" style="width: 2rem; height: 2rem;" />
                @elseif(str_contains($mime, 'pdf') || strtolower($extension) === 'pdf')
                    <x-heroicon-o-document-text class="w-8 h-8" style="width: 2rem; height: 2rem;" />
                @else
                    <x-heroicon-o-document class="w-8 h-8" style="width: 2rem; height: 2rem;" />
                @endif
            </div>

            <!-- File Info -->
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate" title="{{ basename($file['path']) }}">
                    {{ basename($file['path']) }}
                </h4>
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <span class="uppercase font-semibold bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-[10px]">{{ pathinfo($file['path'], PATHINFO_EXTENSION) }}</span>
                    @if(isset($file['size']) && $file['size'] > 0)
                        <span>&bull;</span>
                        <span>{{ number_format($file['size'] / 1024, 1) }} KB</span>
                    @endif
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-2 z-20 relative">
                 <!-- Preview Action (Both View & Edit) -->
                <button 
                    type="button"
                    wire:click.stop="selectFile(@js($file['path']))"
                    class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-all"
                    title="Ver archivo"
                >
                    <x-heroicon-m-eye class="w-5 h-5" style="width: 1.25rem; height: 1.25rem;" />
                </button>
                
                @if($mode === 'edit' && $recordId)
                <!-- Delete Action (Only Edit) -->
                <button 
                    type="button"
                    wire:click.stop="deleteEvidence(@js($file['path']), @js($recordId))"
                    wire:confirm="¿Estás seguro de que deseas eliminar este archivo? Esta acción no se puede deshacer."
                    class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-full transition-all"
                    title="Eliminar archivo"
                >
                    <x-heroicon-m-trash class="w-5 h-5" style="width: 1.25rem; height: 1.25rem;" />
                </button>
                @endif
            </div>

            <!-- Full Card Click Area (Opens Modal) -->
            <button 
                type="button"
                wire:click="selectFile(@js($file['path']))"
                class="absolute inset-0 w-full h-full rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-800 z-10"
            >
                <span class="sr-only">Ver {{ basename($file['path']) }}</span>
            </button>
        </div>
    @endforeach
</div>

@if(count($files) === 0)
    <div class="rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 p-6 text-center bg-gray-50 dark:bg-gray-900/50">
        <x-heroicon-o-document-magnifying-glass class="mx-auto h-8 w-8 text-gray-400" style="width: 2rem; height: 2rem; margin: 0 auto;" />
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No hay evidencias cargadas.</p>
    </div>
@endif

<!-- Modal Logic (Only rendered if edit mode or if explicitly included, but safer to include always as it depends on Livewire state) -->
@if($mode === 'edit' || $mode === 'view')
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
                            href="{{ route('google-drive.download', ['path' => $this->uploadedFilePath]) }}" 
                            class="p-2 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                            title="Descargar"
                        >
                            <x-heroicon-m-arrow-down-tray class="w-5 h-5" style="width: 1.25rem; height: 1.25rem;" />
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
                    @php
                        $mime = 'application/octet-stream';
                        try {
                             $ext = strtolower(pathinfo($this->uploadedFilePath, PATHINFO_EXTENSION));
                             // Asegurar que solo PDFs e imágenes se intenten previsualizar
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
@endif
