<?php

namespace App\Filament\Traits;

use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use App\Models\ActivityExecution;

trait HasEvidencePreview
{
    public $uploadedFileUrl = null;
    public $uploadedFilePath = null;

    public function getEvidenceFiles($record)
    {
        $files = [];
        $evidences = $record->evidencia;
        
        if (is_string($evidences)) {
            $decoded = json_decode($evidences, true);
            $evidences = is_array($decoded) ? $decoded : [$evidences];
        }

        if (is_array($evidences)) {
            foreach ($evidences as $path) {
                try {
                    $disk = Storage::disk('google');
                    $files[] = [
                        'path' => $path,
                        'name' => basename($path),
                        'mime' => $disk->mimeType($path) ?? 'application/octet-stream',
                        'size' => $disk->size($path) ?? 0,
                    ];
                } catch (\Exception $e) {
                     $files[] = [
                        'path' => $path,
                        'name' => basename($path),
                        'mime' => 'application/octet-stream',
                        'size' => 0,
                    ];
                }
            }
        }
        return $files;
    }

    public function selectFile($path)
    {
        $this->uploadedFilePath = $path;
        // Usamos la ruta de previsualización segura que creamos en web.php
        $this->uploadedFileUrl = route('google-drive.preview', ['path' => $path]);
    }

    public function deleteEvidence(string $path, int $executionId)
    {
        $execution = ActivityExecution::find($executionId);
        if (!$execution) {
             Notification::make()
                ->title('Error')
                ->body('No se encontró la ejecución asociada.')
                ->danger()
                ->send();
            return;
        }

        try {
            // 1. Eliminar de Google Drive
            $disk = Storage::disk('google');
            if ($disk->exists($path)) {
                $disk->delete($path);
            }

            // 2. Actualizar registro en base de datos
            $evidences = $execution->evidencia;
            if (is_string($evidences)) {
                $decoded = json_decode($evidences, true);
                $evidences = is_array($decoded) ? $decoded : [$evidences];
            }
            $evidences = is_array($evidences) ? $evidences : [];

            // Filtrar el path eliminado
            $newEvidences = array_values(array_filter($evidences, function ($p) use ($path) {
                return $p !== $path;
            }));

            $execution->update(['evidencia' => $newEvidences]);

            // 3. Limpiar vista previa si es el archivo actual
            if ($this->uploadedFilePath === $path) {
                $this->uploadedFilePath = null;
                $this->uploadedFileUrl = null;
            }

            Notification::make()
                ->title('Evidencia eliminada')
                ->success()
                ->send();

            // 4. Refrescar el formulario si es posible
            // En contextos de página, esto puede requerir $this->fillForm()
            if (method_exists($this, 'fillForm')) {
                $this->fillForm();
            }
            
            // En contextos de acción modal, el refresco puede ser automático al actualizarse el modelo
            // pero si no, podríamos necesitar emitir un evento o similar.

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al eliminar evidencia')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
