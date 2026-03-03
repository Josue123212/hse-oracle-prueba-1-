<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n🚀 Iniciando prueba de conexión con Google Drive...\n";

try {
    // 1. Verificar configuración
    $config = config('filesystems.disks.google');
    echo "Configuración cargada (sin secretos):\n";
    echo "- Driver: " . ($config['driver'] ?? 'N/A') . "\n";
    echo "- Folder ID: " . ($config['folderId'] ?? 'VACÍO') . "\n";
    
    if (empty($config['refreshToken'])) {
        echo "❌ ERROR: Refresh Token está vacío en .env\n";
        exit;
    }

    // 2. Crear archivo temporal
    $tempFile = tempnam(sys_get_temp_dir(), 'test_upload');
    file_put_contents($tempFile, "Prueba de subida Trae AI - " . date('Y-m-d H:i:s'));
    
    $path = "test_folder/prueba_" . time() . ".txt";
    echo "Intentando subir a: $path\n";

    // 3. Subir
    // Usamos put() en lugar de putFileAs para simular lo más básico primero
    $result = Storage::disk('google')->put($path, file_get_contents($tempFile));

    if ($result) {
        echo "✅ Subida exitosa (según Laravel).\n";
        
        // 4. Verificar existencia
        if (Storage::disk('google')->exists($path)) {
            echo "✅ El archivo existe en el disco 'google'.\n";
            $url = Storage::disk('google')->url($path);
            echo "🔗 URL del archivo: $url\n";
        } else {
            echo "⚠️  El archivo se subió pero 'exists()' dice FALSE inmediatamente después.\n";
        }

        // 5. Listar archivos en la raíz (o carpeta configurada) para ver dónde cayó
        echo "\n📂 Listando archivos en la raíz del disco:\n";
        $files = Storage::disk('google')->files('/');
        foreach ($files as $f) {
            echo "- $f\n";
        }

    } else {
        echo "❌ Falló Storage::put(). Devuelve false.\n";
    }

    unlink($tempFile);

} catch (\Exception $e) {
    echo "\n💥 EXCEPCIÓN: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
