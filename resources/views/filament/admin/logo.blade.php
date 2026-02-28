<?php
    $logoUrl = 'https://i.ibb.co/XxdrPS6n/Logo-2.png';
    $brandAlt = config('hse_theme.brand.alt', 'ORACLE PERU S.A.C.');
    $brandTagline = config('hse_theme.brand.tagline', 'Gestión GHSE');
    $fontFamily = config('hse_theme.font_family', 'Inria Sans');
?>
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.25rem 0.75rem;gap:0.4rem;">
    <img src="{{ $logoUrl }}" alt="{{ $brandAlt }}" width="788" height="317" style="height:3.25rem;width:auto;display:block;object-fit:contain" loading="eager" fetchpriority="high">
    <span style="font-family:'{{ $fontFamily }}',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:0.75rem;line-height:1rem;color:rgba(15,23,42,0.7);">
        {{ $brandTagline }}
    </span>
</div>
