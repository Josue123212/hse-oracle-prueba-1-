<?php
    $compactPath = config('hse_theme.sidebar.compact_logo_path');
    $brandLogo = $compactPath ? base_path($compactPath) : base_path(config('hse_theme.brand.logo_path', 'Logo-2.png'));
    $dataUri = null;
    if (is_file($brandLogo)) {
        $data = file_get_contents($brandLogo);
        if ($data !== false) {
            $dataUri = 'data:image/png;base64,' . base64_encode($data);
        }
    }
    $alt = config('hse_theme.brand.alt', 'ORACLE PERU S.A.C.');
    $tagline = config('hse_theme.brand.tagline', 'Gestión GHSE');
?>
<div class="hse-sidebar-brand">
    @if ($dataUri)
        <img class="hse-sidebar-brand__logo" src="{{ $dataUri }}" alt="{{ $alt }}">
    @endif
    <span class="hse-sidebar-brand__text">{{ $tagline }}</span>
    <a class="hse-sidebar-brand__link" href="{{ url('/admin') }}">Inicio</a>
    </div>
