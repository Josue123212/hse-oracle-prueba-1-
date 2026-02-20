<?php
    $logoPath = base_path(config('hse_theme.brand.logo_path', 'Logo-2.png'));
    $dataUri = null;
    if (is_file($logoPath)) {
        $data = file_get_contents($logoPath);
        if ($data !== false) {
            $dataUri = 'data:image/png;base64,' . base64_encode($data);
        }
    }

    $brandAlt = config('hse_theme.brand.alt', 'ORACLE PERU S.A.C.');
    $brandTagline = config('hse_theme.brand.tagline', 'Gestión GHSE');
    $fontFamily = config('hse_theme.font_family', 'Inria Sans');
?>
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.25rem 0.75rem;gap:0.4rem;">
    @if ($dataUri)
        <img src="{{ $dataUri }}" alt="{{ $brandAlt }}" style="height:3.25rem;width:auto;display:block;object-fit:contain">
    @endif
    <span style="font-family:'{{ $fontFamily }}',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:0.75rem;line-height:1rem;color:rgba(15,23,42,0.7);">
        {{ $brandTagline }}
    </span>
</div>
