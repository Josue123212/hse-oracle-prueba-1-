<div x-data="{ 
    activeFile: null, 
    files: @js($files) 
}" class="flex flex-col h-full" style="min-height: 600px;">

    <div class="flex h-full gap-4">
        <!-- Sidebar: Lista de archivos -->
        <div class="w-1/4 border-r border-gray-200 pr-4 overflow-y-auto bg-gray-50 rounded-lg p-2 max-h-[70vh]">
            <h3 class="font-bold mb-4 text-gray-700 px-2">Archivos Adjuntos</h3>
            
            <template x-for="(file, index) in files" :key="index">
                <button 
                    @click="activeFile = file"
                    class="w-full text-left p-3 rounded-lg mb-2 transition-all duration-200 border flex items-center gap-3"
                    :class="activeFile && activeFile.path === file.path ? 'bg-white border-primary-500 ring-1 ring-primary-500 shadow-md' : 'bg-white border-gray-200 hover:bg-gray-50 hover:border-gray-300'"
                >
                    <!-- Icono según tipo -->
                    <div class="flex-shrink-0">
                        <template x-if="['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(file.extension)">
                            <x-heroicon-o-photo class="w-6 h-6 text-blue-500"/>
                        </template>
                        <template x-if="file.extension === 'pdf'">
                            <x-heroicon-o-document-text class="w-6 h-6 text-red-500"/>
                        </template>
                        <template x-if="!['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'].includes(file.extension)">
                            <x-heroicon-o-document class="w-6 h-6 text-gray-400"/>
                        </template>
                    </div>
                    
                    <div class="flex flex-col min-w-0">
                        <span x-text="file.name" class="truncate text-sm font-medium text-gray-900 block w-full"></span>
                        <span x-text="file.extension.toUpperCase()" class="text-xs text-gray-500"></span>
                    </div>
                </button>
            </template>

            <div x-show="files.length === 0" class="text-center py-8 text-gray-500 text-sm italic bg-white rounded border border-dashed border-gray-300 mx-2">
                No hay evidencias adjuntas.
            </div>
        </div>

        <!-- Main: Visualizador -->
        <div class="w-3/4 h-[70vh] flex flex-col bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden relative">
            <!-- Header del visualizador -->
            <div class="flex justify-between items-center p-3 border-b border-gray-200 bg-gray-50 h-14" x-show="activeFile">
                <div class="flex items-center gap-2 overflow-hidden px-2">
                    <span class="font-semibold text-gray-800 truncate max-w-[400px]" x-text="activeFile.name"></span>
                </div>
                <a :href="activeFile.url" target="_blank" download class="flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4"/>
                    <span>Descargar</span>
                </a>
            </div>

            <!-- Contenido -->
            <div class="flex-1 relative bg-gray-100 overflow-hidden flex items-center justify-center p-4">
                <template x-if="activeFile">
                    <div class="w-full h-full flex flex-col items-center justify-center">
                        <!-- Vista Previa Imagen -->
                        <template x-if="['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(activeFile.extension)">
                            <img :src="activeFile.url" class="max-w-full max-h-full object-contain shadow-lg rounded border border-gray-200 bg-white" alt="Vista previa">
                        </template>

                        <!-- Vista Previa PDF -->
                        <template x-if="activeFile.extension === 'pdf'">
                            <iframe :src="activeFile.url" class="w-full h-full border-0 rounded shadow-sm bg-white"></iframe>
                        </template>

                        <!-- Fallback -->
                        <template x-if="!['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'].includes(activeFile.extension)">
                            <div class="flex flex-col items-center justify-center h-full text-gray-500 p-8 text-center bg-white rounded-xl shadow-sm border border-gray-200 max-w-md">
                                <div class="bg-gray-50 p-4 rounded-full mb-4">
                                    <x-heroicon-o-eye-slash class="w-12 h-12 text-gray-400"/>
                                </div>
                                <p class="text-lg font-semibold text-gray-800">Vista previa no disponible</p>
                                <p class="text-sm mt-2 text-gray-500 mb-6">Este tipo de archivo no se puede previsualizar aquí.</p>
                                <a :href="activeFile.url" target="_blank" class="text-primary-600 hover:text-primary-800 hover:underline font-medium flex items-center gap-1">
                                    Abrir en nueva pestaña
                                    <x-heroicon-m-arrow-top-right-on-square class="w-4 h-4"/>
                                </a>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!activeFile">
                    <div class="flex flex-col items-center justify-center text-gray-400 h-full">
                        <div class="bg-gray-50 p-6 rounded-full mb-4 border border-gray-100">
                            <x-heroicon-o-document-magnifying-glass class="w-16 h-16 text-gray-300"/>
                        </div>
                        <p class="text-lg font-medium text-gray-500">Seleccione un archivo de la lista para visualizarlo.</p>
                        <p class="text-sm text-gray-400 mt-2">Puede descargar los archivos seleccionados.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
