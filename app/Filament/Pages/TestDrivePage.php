<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use BackedEnum;

class TestDrivePage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Prueba Drive';

    protected static ?string $title = 'Prueba de Carga a Google Drive';

    protected string $view = 'test.test-drive';

    public ?string $uploadedFileUrl = null;
    public ?string $uploadedFilePath = null;
    public ?string $uploadedFileMime = null;
    public array $filesList = [];

    public function mount(): void
    {
        $this->refreshFilesList();
    }

    public function refreshFilesList(): void
    {
        try {
            $disk = Storage::disk('google');
            // Obtener archivos de test-uploads y raíz (limitado para no sobrecargar)
            $files = $disk->files('test-uploads');
            
            // Si no hay carpeta test-uploads, buscar en raíz pero filtrar
            if (empty($files)) {
                // $files = array_slice($disk->files(), 0, 20); // Opcional
            }

            $this->filesList = collect($files)->map(function ($path) use ($disk) {
                return [
                    'path' => $path,
                    'name' => basename($path),
                    'mime' => $disk->mimeType($path),
                    'size' => $disk->size($path),
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error("Error listando archivos: " . $e->getMessage());
            $this->filesList = [];
        }
    }

    public function selectFile(string $path): void
    {
        try {
            $disk = Storage::disk('google');
            if ($disk->exists($path)) {
                $this->uploadedFilePath = $path;
                $this->uploadedFileMime = $disk->mimeType($path);
                
                // Obtener URL base del driver
                $url = $disk->url($path);
                
                // Intentar convertir URL de descarga/view a URL de preview
                // Formato típico de descarga: https://drive.google.com/uc?id=XXX&export=download
                // Formato de preview requerido: https://drive.google.com/file/d/XXX/preview
                
                $fileId = null;
                $parsedUrl = parse_url($url);
                
                if (isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $queryParams);
                    if (isset($queryParams['id'])) {
                        $fileId = $queryParams['id'];
                    }
                }
                
                if ($fileId) {
                    $this->uploadedFileUrl = "https://drive.google.com/file/d/{$fileId}/preview";
                } else {
                    // Si no se encuentra ID en query params, usar la URL original
                    // Esto puede suceder si la URL ya es un enlace directo o tiene otro formato
                    $this->uploadedFileUrl = $url;
                }

                Log::info("File selected: $path | Original URL: $url | Preview URL: {$this->uploadedFileUrl}");
            }
        } catch (\Exception $e) {
            Log::error("Error selecting file: " . $e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Error al seleccionar archivo')
                ->body('No se pudo generar la vista previa del archivo.')
                ->danger()
                ->send();
        }
    }

    public function downloadFile(string $path)
    {
        try {
            $disk = Storage::disk('google');
            if ($disk->exists($path)) {
                return $disk->download($path);
            }
            
            \Filament\Notifications\Notification::make()
                ->title('Archivo no encontrado')
                ->danger()
                ->send();
                
        } catch (\Exception $e) {
            Log::error("Error downloading file: " . $e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Error al descargar archivo')
                ->body('No se pudo descargar el archivo. Por favor intente nuevamente.')
                ->danger()
                ->send();
        }
    }

    public function deleteFile(string $path)
    {
        try {
            $disk = Storage::disk('google');
            if ($disk->exists($path)) {
                $disk->delete($path);
                
                \Filament\Notifications\Notification::make()
                    ->title('Archivo eliminado correctamente')
                    ->success()
                    ->send();
                    
                $this->refreshFilesList();
                
                // Si el archivo eliminado era el que se estaba mostrando, limpiar la vista previa
                if ($this->uploadedFilePath === $path) {
                    $this->uploadedFilePath = null;
                    $this->uploadedFileUrl = null;
                    $this->uploadedFileMime = null;
                }
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('Archivo no encontrado')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Log::error("Error deleting file: " . $e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Error al eliminar archivo')
                ->body('No se pudo eliminar el archivo: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function uploadAction(): Action
    {
        return Action::make('upload')
            ->label('Subir Archivo de Prueba')
            ->modalHeading('Prueba de Carga a Google Drive')
            ->form([
                FileUpload::make('file')
                    ->label('Seleccionar Archivo')
                    ->disk('google')
                    ->directory('test-uploads') // Directorio simple para prueba
                    ->visibility('private')
                    ->required(),
            ])
            ->action(function (array $data) {
                Log::info('--- INICIO DE PRUEBA DE CARGA ---');
                Log::info('Datos recibidos del formulario:', $data);

                $filePath = $data['file'] ?? null;

                if (!$filePath) {
                    Log::error('No se recibió ninguna ruta de archivo en $data["file"]');
                    return;
                }

                Log::info("Ruta del archivo reportada por Livewire/Filament: {$filePath}");

                try {
                    // Verificar si el archivo existe en el disco 'google'
                    $disk = Storage::disk('google');
                    
                    if ($disk->exists($filePath)) {
                        Log::info("¡ÉXITO! El archivo existe en Google Drive en la ruta: {$filePath}");
                        
                        // Obtener metadatos para confirmar
                        $size = $disk->size($filePath);
                        $mime = $disk->mimeType($filePath);
                        Log::info("Tamaño: {$size} bytes, Tipo MIME: {$mime}");
                        
                        // Intentar listar el contenido del directorio para ver si aparece
                        $directory = dirname($filePath);
                        $filesInDir = $disk->files($directory);
                        Log::info("Archivos en el directorio {$directory}: " . implode(', ', $filesInDir));

                        // Actualizar estado para la vista
                        $this->uploadedFilePath = $filePath;
                        $this->uploadedFileMime = $mime;
                        try {
                            $this->uploadedFileUrl = $disk->url($filePath);
                            Log::info("URL generada: {$this->uploadedFileUrl}");
                        } catch (\Exception $e) {
                            Log::error("Error generando URL: " . $e->getMessage());
                            $this->uploadedFileUrl = null;
                        }

                    } else {
                        Log::error("FALLO: El archivo NO se encuentra en Google Drive en la ruta: {$filePath}");
                        
                        // Intentar buscar en la raíz por si acaso
                        $filesRoot = $disk->files();
                        Log::info("Listado de archivos en la raíz de Drive (primeros 10):", array_slice($filesRoot, 0, 10));
                    }

                } catch (\Exception $e) {
                    Log::error("EXCEPCIÓN durante la verificación: " . $e->getMessage());
                    Log::error($e->getTraceAsString());
                }

                Log::info('--- FIN DE PRUEBA DE CARGA ---');
                
                // Notificar al usuario
                \Filament\Notifications\Notification::make()
                    ->title('Proceso de prueba finalizado')
                    ->body('Revisa los logs de Laravel para ver los detalles de depuración.')
                    ->success()
                    ->send();
                    
                $this->refreshFilesList();
            });
    }
}
