<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileStorageService
{
    public function storePublic(string $path, string $contents): string
    {
        Storage::disk('public')->put($path, $contents);
        return Storage::disk('public')->url($path);
    }

    public function deletePublic(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }

    public function listPublic(string $dir): array
    {
        return Storage::disk('public')->files($dir);
    }

    public function urlPublic(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}

