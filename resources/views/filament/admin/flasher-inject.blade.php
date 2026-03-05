@if(session()->has('flasher::messages'))
    {!! flasher_render() !!}
@endif
{{-- Inyección manual de scripts de Flasher (Core + Toastr) --}}
{{-- Toastr requiere jQuery --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- Toastr Original (Solución Definitiva) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

{{-- Flasher (Solo para mantener compatibilidad si algo más lo usa) --}}
<script src="https://cdn.jsdelivr.net/npm/@flasher/flasher@1.3.2/dist/flasher.min.js"></script>

<script>
    // Configuración global de Toastr
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };
        console.log('🥂 Toastr Original Loaded & Configured');
    }
</script>