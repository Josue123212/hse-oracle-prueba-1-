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
        // VOLVEMOS AL BORRADO INMEDIATO (Robustecido)
        // Se ejecuta la acción WORM al momento del click.
        
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
            // Decodificar URL
            $decodedPath = urldecode($path);

            // 1. Intentar Revocación WORM
                $evidenceRecord = \App\Models\ExecutionEvidence::where(function($query) use ($path, $decodedPath) {
                        $query->where('file_path', $path)
                              ->orWhere('file_path', $decodedPath);
                    })
                    ->whereNull('revoked_at') // Priorizar registros activos
                    ->latest()
                    ->first();
                
                // Fallback: búsqueda por nombre si el path no coincide exacto
                if (!$evidenceRecord) {
                    $filename = basename($path);
                    $evidenceRecord = \App\Models\ExecutionEvidence::where('execution_id', $executionId)
                        ->where('file_name', $filename)
                        ->whereNull('revoked_at')
                        ->latest()
                        ->first();
                }

            if ($evidenceRecord) {
                // WORM: Revocar lógicamente
                app(\App\Services\EvidenceStorageService::class)->revokeEvidence(
                    $evidenceRecord,
                    auth()->id() ?? 0,
                    "Removed via web interface (Direct Action)"
                );
            } else {
                // Legacy: Borrar físico si no hay registro BD
                $disk = Storage::disk('google');
                if ($disk->exists($path)) {
                    $disk->delete($path);
                } elseif ($disk->exists($decodedPath)) {
                    $disk->delete($decodedPath);
                }
            }

            // 2. IMPORTANTE: Actualizar el campo JSON 'evidencia' en la tabla ActivityExecution
            // Esto es necesario porque el campo 'evidencia' es lo que usa el frontend para pintar la lista.
            // Aunque hayamos revocado el registro en la tabla relacionada, el JSON sigue teniendo el path.
            
            $evidences = $execution->evidencia;
            if (is_string($evidences)) {
                $decoded = json_decode($evidences, true);
                $evidences = is_array($decoded) ? $decoded : [$evidences];
            }
            $evidences = is_array($evidences) ? $evidences : [];

            // Filtrar el path eliminado
            $newEvidences = array_values(array_filter($evidences, function ($p) use ($path, $decodedPath) {
                return $p !== $path && $p !== $decodedPath;
            }));

            $execution->update(['evidencia' => $newEvidences]);

            // 3. Notificación y Refresco
            Notification::make()
                ->title('Evidencia eliminada')
                ->success()
                ->send();
                
            // Intentar refrescar el estado del componente Livewire si es posible
            if (method_exists($this, 'fillForm')) {
                $this->fillForm();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al eliminar evidencia')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
