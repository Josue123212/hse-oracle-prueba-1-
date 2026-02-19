<?php

use App\Services\FileStorageService;
use Illuminate\Support\Facades\Storage;

it('guarda contenido en public y devuelve url', function () {
    Storage::fake('public');

    $service = new FileStorageService();
    $path = 'inspecciones/2026/02/prueba.pdf';
    $url = $service->storePublic($path, 'contenido');

    Storage::disk('public')->assertExists($path);
    expect($url)->toContain('/storage/inspecciones/2026/02/prueba.pdf');
});

it('obtiene url pública a partir del path', function () {
    Storage::fake('public');
    $service = new FileStorageService();

    $path = 'programas/2026/02/archivo.pdf';
    Storage::disk('public')->put($path, 'contenido');

    $url = $service->urlPublic($path);
    expect($url)->toContain('/storage/programas/2026/02/archivo.pdf');
});

