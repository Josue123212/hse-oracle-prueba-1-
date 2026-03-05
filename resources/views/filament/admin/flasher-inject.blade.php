@if(session()->has('flasher::messages'))
    {!! flasher_render() !!}
@endif
{{-- Inyección manual de scripts de Flasher (Core + Toastr) --}}
{{-- Toastr requiere jQuery para funcionar correctamente --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@flasher/flasher@1.3.2/dist/flasher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@flasher/flasher-toastr@1.3.2/dist/flasher-toastr.min.js"></script>
<script>
    console.log('Flasher Core loaded:', typeof flasher !== 'undefined' ? 'YES' : 'NO');
    console.log('Flasher Toastr loaded:', typeof flasher !== 'undefined' && typeof flasher.addFactory !== 'undefined' ? 'YES' : 'NO');
</script>