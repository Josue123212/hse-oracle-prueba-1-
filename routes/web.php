<?php

use Illuminate\Support\Facades\Route;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/google-drive/download', function (Request $request) {
    $path = $request->query('path');
    
    if (!$path) {
        abort(400, 'Path is required');
    }

    $disk = Storage::disk('google');

    if (!$disk->exists($path)) {
        abort(404, 'File not found');
    }

    $name = basename($path);
    $mimeType = $disk->mimeType($path);
    $size = $disk->size($path);

    // Stream the download to avoid memory issues and potential security flags with large blobs
    return response()->streamDownload(function () use ($disk, $path) {
        echo $disk->get($path);
    }, $name, [
        'Content-Type' => $mimeType,
        'Content-Length' => $size,
        'Content-Disposition' => 'attachment; filename="' . $name . '"',
        'Cache-Control' => 'no-cache, private',
    ]);
})->name('google-drive.download')->middleware('auth');

Route::get('/google-drive/preview', function (Request $request) {
    $path = $request->query('path');
    
    if (!$path) {
        abort(400, 'Path is required');
    }

    $disk = Storage::disk('google');

    if (!$disk->exists($path)) {
        abort(404, 'File not found');
    }

    $name = basename($path);
    $mimeType = $disk->mimeType($path);
    $size = $disk->size($path);

    // Stream for preview (inline disposition)
    return response()->stream(function () use ($disk, $path) {
        echo $disk->get($path);
    }, 200, [
        'Content-Type' => $mimeType,
        'Content-Length' => $size,
        'Content-Disposition' => 'inline; filename="' . $name . '"',
        'Cache-Control' => 'no-cache, private',
    ]);
})->name('google-drive.preview')->middleware('auth');

Route::get('/programs/{program}/pdf', [\App\Http\Controllers\ProgramPdfController::class, 'download'])
    ->name('programs.pdf')
    ->middleware('auth');

Route::get('/programs/{program}/excel', [\App\Http\Controllers\ProgramPdfController::class, 'downloadExcel'])
    ->name('programs.excel')
    ->middleware('auth');

