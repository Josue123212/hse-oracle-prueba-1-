<x-filament-panels::page>
    {{-- Inyectar scripts de Livewire Alert --}}
    {{-- @livewireScriptConfig removed as Filament handles this --}}

    {{-- Estilos personalizados para nuestros componentes custom --}}
    <style>
        .custom-glass-modal {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .custom-toast-pill {
            background: #1f2937;
            color: white;
            border-radius: 9999px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>

    {{-- SECCIÓN 1: ALERTAS (Modales e Interrupciones) --}}
    <x-filament::section>
        <x-slot name="heading">
            1. Tipos de Alertas (Modales)
        </x-slot>
        <x-slot name="description">
            Alertas que requieren atención inmediata del usuario o confirmación.
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Propuesta 1: Nativa --}}
            <div class="flex flex-col gap-2">
                <div class="h-32 bg-gray-50 rounded-lg flex items-center justify-center border-2 border-dashed border-gray-300">
                    <div class="text-center">
                        <x-heroicon-o-shield-check class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                        <span class="text-xs text-gray-500 font-mono">Filament Native</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="alertNative('success')" class="py-1 px-2 bg-green-600 hover:bg-green-500 text-white rounded text-xs">Éxito</button>
                    <button type="button" wire:click="alertNative('error')" class="py-1 px-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs">Error</button>
                    <button type="button" wire:click="alertNative('warning')" class="py-1 px-2 bg-yellow-500 hover:bg-yellow-400 text-white rounded text-xs">Alerta</button>
                    <button type="button" wire:click="alertNative('info')" class="py-1 px-2 bg-blue-500 hover:bg-blue-400 text-white rounded text-xs">Info</button>
                </div>
                <p class="text-xs text-gray-500 text-center">Modal de confirmación estándar.</p>
            </div>

            {{-- Propuesta 2: SweetAlert2 --}}
            <div class="flex flex-col gap-2">
                <div class="h-32 bg-indigo-50 rounded-lg flex items-center justify-center border-2 border-indigo-100">
                    <div class="text-center">
                        <span class="text-2xl mb-1 block">🍭</span>
                        <span class="text-xs text-indigo-500 font-mono">SweetAlert2</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="alertSweet('success')" class="py-1 px-2 bg-green-600 hover:bg-green-500 text-white rounded text-xs">Éxito</button>
                    <button type="button" wire:click="alertSweet('error')" class="py-1 px-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs">Error</button>
                    <button type="button" wire:click="alertSweet('warning')" class="py-1 px-2 bg-yellow-500 hover:bg-yellow-400 text-white rounded text-xs">Alerta</button>
                    <button type="button" wire:click="alertSweet('info')" class="py-1 px-2 bg-blue-500 hover:bg-blue-400 text-white rounded text-xs">Info</button>
                </div>
                <p class="text-xs text-gray-500 text-center">Librería externa con animaciones.</p>
            </div>

            {{-- Propuesta 3: Custom Glass --}}
            <div class="flex flex-col gap-2">
                <div class="h-32 bg-gradient-to-br from-rose-50 to-orange-50 rounded-lg flex items-center justify-center border border-rose-100">
                    <div class="text-center">
                        <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-rose-400 mx-auto mb-2" />
                        <span class="text-xs text-rose-500 font-mono">Custom Glass</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="alertCustom('success')" class="py-1 px-2 bg-green-600 hover:bg-green-500 text-white rounded text-xs">Éxito</button>
                    <button type="button" wire:click="alertCustom('error')" class="py-1 px-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs">Error</button>
                    <button type="button" wire:click="alertCustom('warning')" class="py-1 px-2 bg-yellow-500 hover:bg-yellow-400 text-white rounded text-xs">Alerta</button>
                    <button type="button" wire:click="alertCustom('info')" class="py-1 px-2 bg-blue-500 hover:bg-blue-400 text-white rounded text-xs">Info</button>
                </div>
                <p class="text-xs text-gray-500 text-center">Diseño moderno glassmorphism.</p>
            </div>

            {{-- Propuesta 4: PHP Flasher --}}
            <div class="flex flex-col gap-2">
                <div class="h-32 bg-slate-900 rounded-lg flex items-center justify-center border border-slate-700">
                    <div class="text-center">
                        <span class="text-2xl mb-1 block">🔔</span>
                        <span class="text-xs text-green-400 font-mono">PHP Flasher</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="alertFlasher('success')" class="py-1 px-2 bg-green-600 hover:bg-green-500 text-white rounded text-xs">Éxito</button>
                    <button type="button" wire:click="alertFlasher('error')" class="py-1 px-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs">Error</button>
                    <button type="button" wire:click="alertFlasher('warning')" class="py-1 px-2 bg-yellow-500 hover:bg-yellow-400 text-white rounded text-xs">Alerta</button>
                    <button type="button" wire:click="alertFlasher('info')" class="py-1 px-2 bg-blue-500 hover:bg-blue-400 text-white rounded text-xs">Info</button>
                </div>
                <p class="text-xs text-gray-500 text-center">Notificaciones robustas y configurables.</p>
            </div>
        </div>
    </x-filament::section>

    {{-- SECCIÓN 2: NOTIFICACIONES (Toasts temporales) --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            2. Tipos de Notificaciones (Toasts)
        </x-slot>
        <x-slot name="description">
            Mensajes temporales no intrusivos para feedback de operaciones.
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Propuesta 1: Nativa --}}
            <div class="flex flex-col gap-2">
                <div class="h-24 bg-white rounded-lg shadow-sm border-l-4 border-success-500 p-4 flex items-center">
                    <div class="flex-1">
                        <div class="h-2 w-20 bg-gray-200 rounded mb-2"></div>
                        <div class="h-2 w-32 bg-gray-100 rounded"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="notificationNative('success')" class="py-1 px-2 bg-green-600 hover:bg-green-500 text-white rounded text-xs">Éxito</button>
                    <button type="button" wire:click="notificationNative('error')" class="py-1 px-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs">Error</button>
                    <button type="button" wire:click="notificationNative('warning')" class="py-1 px-2 bg-yellow-500 hover:bg-yellow-400 text-white rounded text-xs">Alerta</button>
                    <button type="button" wire:click="notificationNative('info')" class="py-1 px-2 bg-blue-500 hover:bg-blue-400 text-white rounded text-xs">Info</button>
                </div>
            </div>

            {{-- Propuesta 2: SweetAlert Toast --}}
            <div class="flex flex-col gap-2">
                <div class="h-24 bg-white rounded-lg shadow-md p-3 flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-success-100 flex items-center justify-center text-xs text-success-600">✓</div>
                    <div class="flex-1 pt-1">
                        <div class="h-2 w-24 bg-gray-200 rounded"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="notificationSweet('success')" class="py-1 px-2 bg-green-600 hover:bg-green-500 text-white rounded text-xs">Éxito</button>
                    <button type="button" wire:click="notificationSweet('error')" class="py-1 px-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs">Error</button>
                    <button type="button" wire:click="notificationSweet('warning')" class="py-1 px-2 bg-yellow-500 hover:bg-yellow-400 text-white rounded text-xs">Alerta</button>
                    <button type="button" wire:click="notificationSweet('info')" class="py-1 px-2 bg-blue-500 hover:bg-blue-400 text-white rounded text-xs">Info</button>
                </div>
            </div>

            {{-- Propuesta 3: Custom Pill --}}
            <div class="flex flex-col gap-2">
                <div class="h-24 flex items-center justify-center bg-gray-50 rounded-lg">
                    <div class="px-4 py-2 bg-gray-800 text-white text-xs rounded-full shadow-lg">
                        Mensaje Flotante
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" 
                            wire:click="notificationCustom('success')" 
                            class="py-1 px-2 bg-green-600 hover:bg-green-500 text-white rounded text-xs">Éxito</button>
                    <button type="button" 
                            wire:click="notificationCustom('error')" 
                            class="py-1 px-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs">Error</button>
                    <button type="button" 
                            wire:click="notificationCustom('warning')" 
                            class="py-1 px-2 bg-yellow-500 hover:bg-yellow-400 text-white rounded text-xs">Advertencia</button>
                    <button type="button" 
                            wire:click="notificationCustom('info')" 
                            class="py-1 px-2 bg-blue-600 hover:bg-blue-500 text-white rounded text-xs">Info</button>
                </div>
            </div>

            {{-- Propuesta 4: PHP Flasher Toast --}}
            <div class="flex flex-col gap-2">
                <div class="h-24 bg-slate-800 rounded-lg p-4 border-l-4 border-blue-500 shadow-xl">
                    <div class="h-2 w-16 bg-slate-600 rounded mb-2"></div>
                    <div class="h-2 w-24 bg-slate-700 rounded"></div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="notificationFlasher('success')" class="py-1 px-2 bg-green-600 hover:bg-green-500 text-white rounded text-xs">Éxito</button>
                    <button type="button" wire:click="notificationFlasher('error')" class="py-1 px-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs">Error</button>
                    <button type="button" wire:click="notificationFlasher('warning')" class="py-1 px-2 bg-yellow-500 hover:bg-yellow-400 text-white rounded text-xs">Alerta</button>
                    <button type="button" wire:click="notificationFlasher('info')" class="py-1 px-2 bg-blue-500 hover:bg-blue-400 text-white rounded text-xs">Info</button>
                </div>
            </div>
        </div>
    </x-filament::section>
        
    <div class="mt-6 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">
        <h3 class="font-bold text-lg mb-2">Panel de Depuración</h3>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-white p-3 rounded border">
                <span class="font-semibold">Última Interacción:</span>
                <span class="text-primary-600">{{ $lastInteraction }}</span>
            </div>
            <div class="bg-white p-3 rounded border">
                <span class="font-semibold">Contador de Notificaciones:</span>
                <span class="text-primary-600">{{ $notificationCount }}</span>
            </div>
        </div>
    </div>

    {{-- Listener manual para notificaciones en caso de fallo del interceptor nativo --}}
    <div
        x-data="{}"
        x-init="
            window.addEventListener('manual-notification', (event) => {
                // ... (código existente)
            });
        "
    ></div>

    {{-- Componentes para Custom Alerts/Toasts --}}
    <div
        wire:ignore
        x-data="{ open: false, title: '', body: '', type: 'info' }"
        @open-custom-modal.window="
            console.log('Modal Event:', $event.detail);
            const data = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
            open = true; 
            title = data.title; 
            body = data.body; 
            type = data.type
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

    {{-- Custom Toast Container (Sin x-teleport para prueba radical) --}}
    <div
        wire:ignore
        x-data="{ show: false, message: '', type: 'info', timeout: null }"
        x-init="
            window.addEventListener('show-custom-toast', (event) => {
                const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                if (timeout) clearTimeout(timeout);
                message = data.message; 
                type = data.type; 
                show = true; 
                timeout = setTimeout(() => { show = false; }, 3000);
            });
        "
        style="position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 999999; pointer-events: none;"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-4"
    >
        <div class="pointer-events-auto flex items-center px-5 py-3 gap-3 rounded-full shadow-2xl border-2"
             :style="type === 'success' ? 'border-color: #10b981; background-color: rgba(255, 255, 255, 0.98);' : 
                     (type === 'error' ? 'border-color: #ef4444; background-color: rgba(255, 255, 255, 0.98);' : 
                     (type === 'warning' ? 'border-color: #f59e0b; background-color: rgba(255, 255, 255, 0.98);' : 
                     'border-color: #3b82f6; background-color: rgba(255, 255, 255, 0.98);'))">
            
            <!-- Iconos SVG Dinámicos -->
            <div class="flex-shrink-0">
                <!-- Success Icon -->
                <svg x-show="type === 'success'" class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <!-- Error Icon -->
                <svg x-show="type === 'error'" class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <!-- Warning Icon -->
                <svg x-show="type === 'warning'" class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <!-- Info Icon -->
                <svg x-show="type === 'info'" class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            
            <!-- Mensaje -->
            <div class="flex flex-col">
                <span class="text-xs font-bold uppercase tracking-wider" 
                      :style="type === 'success' ? 'color: #10b981;' : 
                              (type === 'error' ? 'color: #ef4444;' : 
                              (type === 'warning' ? 'color: #f59e0b;' : 'color: #3b82f6;'))"
                      x-text="type"></span>
                <span class="text-sm font-semibold text-gray-800 whitespace-nowrap" x-text="message"></span>
            </div>
        </div>
    </div>

    {{-- Scripts de librerías externas --}}
    {{-- SweetAlert2 CDN --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- Listener para SweetAlert2 manual dispatch --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('swal', (data) => {
                const config = Array.isArray(data) ? data[0] : data;
                Swal.fire(config);
            });
            
            // Listener específico para PHP Flasher en Livewire v3 (Emulación manual)
            Livewire.on('flasher:render', (data) => {
                console.log('🔥 Flasher Event Received:', data);
                const payload = Array.isArray(data) ? data[0] : data;
                
                if (typeof flasher !== 'undefined') {
                    console.log('✅ Flasher JS found. Rendering...');
                    // Intento 1: API moderna
                    try {
                        flasher.render(payload);
                    } catch (e) {
                        console.error('❌ Error rendering Flasher:', e);
                        // Fallback manual si falla el renderizado
                        if (payload.envelopes && payload.envelopes[0]) {
                            const env = payload.envelopes[0].notification;
                            alert(`[Flasher Error Fallback] ${env.title}: ${env.message}`);
                        }
                    }
                } else {
                    console.error('❌ Flasher JS global NOT found.');
                    // Fallback visual simple si la librería no cargó
                    if (payload.envelopes && payload.envelopes[0]) {
                        const env = payload.envelopes[0].notification;
                        alert(`[Flasher Missing Fallback] ${env.title}: ${env.message}`);
                    }
                }
            });
        });
    </script>

    {{-- PHP Flasher --}}
    @if(function_exists('flasher_render'))
        {{ flasher_render() }}
    @endif

</x-filament-panels::page>
