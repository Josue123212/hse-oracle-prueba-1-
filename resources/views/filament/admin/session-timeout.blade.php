@php
    $lifetime = config('session.lifetime') * 60 * 1000; // in milliseconds
    $warningTime = 2 * 60 * 1000; // 2 minutes before expiration
@endphp

<div
    x-data="{
        lifetime: {{ $lifetime }},
        warningTime: {{ $warningTime }},
        startTime: Date.now(),
        showWarning: false,
        timer: null,

        init() {
            this.startTimer();
            
            // Hook into Livewire requests to reset timer on success
            if (window.Livewire) {
                Livewire.hook('request', ({ fail, succeed }) => {
                    succeed(({ status, preventDefault }) => {
                        this.resetTimer();
                    });
                    fail(({ status, preventDefault }) => {
                        // Don't reset on failure?
                    });
                });
            }

            // Also reset on any fetch/XHR if we can (harder), but Livewire is main interaction.
            // We can add listener for 'livewire:navigated' too
            document.addEventListener('livewire:navigated', () => this.resetTimer());
        },

        startTimer() {
            this.showWarning = false;
            if (this.timer) clearInterval(this.timer);
            
            this.timer = setInterval(() => {
                const elapsed = Date.now() - this.startTime;
                const remaining = this.lifetime - elapsed;
                
                if (remaining <= 0) {
                    clearInterval(this.timer);
                    window.location.reload(); // Will redirect to login
                } else if (remaining <= this.warningTime && !this.showWarning) {
                    this.showWarning = true;
                }
            }, 1000);
        },

        resetTimer() {
            this.startTime = Date.now();
            this.showWarning = false;
        },

        extendSession() {
            // Ping the server to keep session alive
            fetch(window.location.href, {
                method: 'HEAD',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(() => {
                this.resetTimer();
                // Close modal explicitly
                this.showWarning = false;
            }).catch(() => {
                // If ping fails (e.g. network error), maybe don't reset?
                // Or maybe session already expired?
                // Reloading is safest if ping fails due to 401/419
                window.location.reload();
            });
        }
    }"
    x-show="showWarning"
    style="display: none;"
    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
>
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl p-6 max-w-md w-full mx-4 border border-gray-200 dark:border-gray-700 relative">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-full text-yellow-600 dark:text-yellow-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sesión por expirar</h3>
        </div>
        
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Tu sesión expirará pronto debido a inactividad. Por favor, confirma si deseas mantenerte conectado para evitar la pérdida de datos no guardados.
        </p>
        
        <div class="flex justify-end gap-3">
            <button 
                @click="window.location.reload()"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors"
            >
                Salir
            </button>
            <button 
                @click="extendSession()"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                style="background-color: rgb(var(--primary-600));"
            >
                Mantener Sesión
            </button>
        </div>
    </div>
</div>
