<div class="w-full" 
     wire:ignore 
     x-data="signaturePadComponent({
        state: $wire.$entangle('{{ $getStatePath() }}')
     })">
    
    <div class="bg-white border rounded-lg overflow-hidden relative" 
         style="border: 2px dashed #ccc; min-height: 160px;">
        <canvas x-ref="canvas" 
                class="w-full h-40 touch-none cursor-crosshair block" 
                style="width: 100%; height: 160px;"></canvas>
                
        <div x-show="!isReady" class="absolute inset-0 flex items-center justify-center bg-gray-50 bg-opacity-75 z-10">
            <span class="text-sm text-gray-500">Cargando panel de firma...</span>
        </div>
    </div>

    <div class="mt-2 flex justify-between items-center">
        <span class="text-xs text-gray-500">Firme dentro del recuadro con su ratón o dedo.</span>
        <button type="button" 
                x-on:click="clear" 
                class="text-xs bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded-lg shadow-sm transition">
            Borrar Firma
        </button>
    </div>

    @once
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        window.signaturePadComponent = function({ state }) {
            return {
                signaturePad: null,
                isReady: false,
                value: state,
                
                init() {
                    this.waitForLib();
                },

                waitForLib() {
                    if (typeof SignaturePad === 'undefined') {
                        setTimeout(() => this.waitForLib(), 200);
                        return;
                    }
                    this.initPad();
                },

                initPad() {
                    const canvas = this.$refs.canvas;
                    
                    // Función para redimensionar el canvas correctamente
                    const resizeCanvas = () => {
                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext('2d').scale(ratio, ratio);
                        
                        // Si hay datos, recargarlos después del resize
                        if (this.value && this.signaturePad) {
                            this.signaturePad.fromDataURL(this.value);
                        }
                    };

                    this.signaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgba(255, 255, 255, 0)',
                        penColor: 'rgb(0, 0, 0)'
                    });

                    // Resize inicial
                    resizeCanvas();
                    // Resize on window resize
                    window.addEventListener('resize', resizeCanvas);

                    this.isReady = true;

                    // Cargar valor inicial
                    if (this.value) {
                        try {
                            this.signaturePad.fromDataURL(this.value);
                        } catch (e) {
                            console.error('Error cargando firma:', e);
                        }
                    }

                    // Escuchar cambios
                    this.signaturePad.addEventListener('endStroke', () => {
                        this.value = this.signaturePad.isEmpty() ? null : this.signaturePad.toDataURL();
                    });
                },

                clear() {
                    this.signaturePad.clear();
                    this.value = null;
                }
            };
        }
    </script>
    @endonce
</div>
