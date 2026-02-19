<?php
    $logoPath = base_path('Logo-2.png');
    $dataUri = null;
    if (is_file($logoPath)) {
        $data = file_get_contents($logoPath);
        if ($data !== false) {
            $dataUri = 'data:image/png;base64,' . base64_encode($data);
        }
    }
?>
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.25rem 0.75rem;gap:0.4rem;">
    @if ($dataUri)
        <img src="{{ $dataUri }}" alt="ORACLE PERU S.A.C." style="height:3.25rem;width:auto;display:block;object-fit:contain">
    @endif
    <span style="font-family:'Inria Sans',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:0.75rem;line-height:1rem;color:rgba(15,23,42,0.7);">
        Gestión GHSE
    </span>
</div>
