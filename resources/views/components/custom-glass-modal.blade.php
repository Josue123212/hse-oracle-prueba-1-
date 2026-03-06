<style>
    /* Custom Glass Style for our custom modal */
    .custom-glass-modal {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    }

    /* Apply Glass Effect to Standard Filament Modals (Confirmation, Forms, etc.) */
    .fi-modal-window {
        background-color: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;
    }

    /* Glass Effect for Filament Notifications (Toasts) */
    .fi-no-notification {
        background-color: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;
    }
    
    /* Dark mode support */
    .dark .fi-modal-window {
        background-color: rgba(24, 24, 27, 0.85) !important; /* zinc-950 with opacity */
        border-color: rgba(255, 255, 255, 0.1);
    }
</style>

<div
    wire:ignore
    x-data="{ open: false, title: '', body: '', type: 'info' }"
    @open-custom-modal.window="
        const data = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
        open = true; 
        title = data.title || 'Alerta'; 
        body = data.body || ''; 
        type = data.type || 'info';
    "
    @notify.window="
        if ($event.detail.status === 'danger') {
            open = true;
            title = 'Atención';
            body = $event.detail.title || 'Faltan campos por llenar. Por favor verifique el formulario.';
            type = 'error';
        }
    "
>
    <template x-teleport="body">
        <div
            x-show="open"
            style="display: none;"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div 
                class="custom-glass-modal w-full max-w-md p-6 rounded-2xl transform transition-all"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                @click.away="open = false"
            >
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4"
                         :class="{
                            'bg-green-100': type === 'success',
                            'bg-red-100': type === 'error',
                            'bg-yellow-100': type === 'warning',
                            'bg-blue-100': type === 'info'
                         }">
                        <template x-if="type === 'success'">
                            <x-heroicon-o-check-circle class="h-6 w-6 text-green-600" />
                        </template>
                        <template x-if="type === 'error'">
                            <x-heroicon-o-x-circle class="h-6 w-6 text-red-600" />
                        </template>
                        <template x-if="type === 'warning'">
                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-yellow-600" />
                        </template>
                        <template x-if="type === 'info'">
                            <x-heroicon-o-information-circle class="h-6 w-6 text-blue-600" />
                        </template>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="title"></h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500" x-text="body"></p>
                    </div>
                    <div class="mt-5 sm:mt-6">
                        <button type="button" @click="open = false" 
                                class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm"
                                :class="{
                                    'bg-green-600 hover:bg-green-700 focus:ring-green-500': type === 'success',
                                    'bg-red-600 hover:bg-red-700 focus:ring-red-500': type === 'error',
                                    'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500': type === 'warning',
                                    'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500': type === 'info'
                                }">
                            Entendido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
