<?php

namespace App\Services;

use App\Models\ActivityExecution;
use App\Models\ExecutionEvidence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EvidenceStorageService
{
    protected string $disk = 'google';

    /**
     * Store a new evidence file.
     * 
     * @param ActivityExecution $execution
     * @param UploadedFile $file
     * @param int $userId
     * @return ExecutionEvidence
     * @throws \Exception
     */
    public function storeEvidence(ActivityExecution $execution, UploadedFile $file, int $userId): ExecutionEvidence
    {
        // 1. Calculate Hash
        $hash = hash_file('sha256', $file->getRealPath());

        // 2. Define Path (Human Readable Structure)
        // Estructura solicitada: evidences/{Year}/{Programa}/{Actividad}/{Fecha}/{NombreOriginal}
        $year = now()->format('Y');
        
        // Obtener nombres descriptivos
        // Nota: Asumimos que la relación 'program' existe en Activity. Si no, usamos fallback.
        $programName = $execution->activity->program->nombre ?? 'Sin_Programa';
        $activityName = $execution->activity->nombre ?? 'Sin_Actividad';
        $execDate = $execution->fecha_programada 
            ? Carbon::parse($execution->fecha_programada)->format('Y-m-d') 
            : now()->format('Y-m-d');

        // Sanitizar para sistema de archivos (permitir espacios y tildes, quitar caracteres prohibidos)
        $safeProgram = $this->sanitizePathSegment($programName);
        $safeActivity = $this->sanitizePathSegment($activityName);
        
        $originalName = $file->getClientOriginalName();
        // $extension = $file->getClientOriginalExtension(); // No needed for exact name
        
        // Requisito usuario: Nombre original exacto, sin hashes.
        // La unicidad se maneja por carpetas (Fecha/Actividad).
        $finalFilename = $originalName;
        
        // Ruta final legible
        $path = "evidences/{$year}/{$safeProgram}/{$safeActivity}/{$execDate}";
        
        // 3. Upload to Storage (Google Drive)
        $storedPath = Storage::disk($this->disk)->putFileAs($path, $file, $finalFilename);

        if (!$storedPath) {
            throw new \Exception("Failed to upload file to storage.");
        }

        // 4. Create Database Record
        return ExecutionEvidence::create([
            'execution_id' => $execution->id,
            'file_path' => $storedPath,
            'file_name' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_hash' => $hash,
            'uploaded_by' => $userId,
            'uploaded_at' => now(),
            'is_replacement' => false,
        ]);
    }

    /**
     * Helper para limpiar nombres de carpetas
     */
    protected function sanitizePathSegment(string $name): string
    {
        // Reemplazar caracteres prohibidos en sistemas de archivos: \ / : * ? " < > |
        // Google Drive es flexible, pero mejor prevenir.
        $clean = preg_replace('/[<>:"\/\\|?*]/', '', $name);
        return trim($clean);
    }

    /**
     * Store evidence from a local path (e.g. from Filament temp upload).
     * 
     * @param ActivityExecution $execution
     * @param string $absolutePath
     * @param string $originalName
     * @param int $userId
     * @return ExecutionEvidence
     * @throws \Exception
     */
    public function storeEvidenceFromPath(ActivityExecution $execution, string $absolutePath, string $originalName, int $userId): ExecutionEvidence
    {
        // 1. Calculate Hash
        $hash = hash_file('sha256', $absolutePath);

        // 2. Define Path (Human Readable Structure)
        // Estructura solicitada: evidences/{Year}/{Programa}/{Actividad}/{Fecha}/{NombreOriginal}
        $year = now()->format('Y');
        
        // Obtener nombres descriptivos
        $programName = $execution->activity->program->nombre ?? 'Sin_Programa';
        $activityName = $execution->activity->nombre ?? 'Sin_Actividad';
        $execDate = $execution->fecha_programada 
            ? Carbon::parse($execution->fecha_programada)->format('Y-m-d') 
            : now()->format('Y-m-d');

        // Sanitizar
        $safeProgram = $this->sanitizePathSegment($programName);
        $safeActivity = $this->sanitizePathSegment($activityName);
        
        // Requisito: Nombre original exacto
        $finalFilename = $originalName;
        
        // Ruta completa
        $path = "evidences/{$year}/{$safeProgram}/{$safeActivity}/{$execDate}/{$finalFilename}";
        
        // 3. Upload to Storage (Google Drive)
        $stream = fopen($absolutePath, 'r');
        // put() recibe el path completo incluyendo el nombre del archivo
        $stored = Storage::disk($this->disk)->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        if (!$stored) {
            throw new \Exception("Failed to upload file to storage.");
        }

        // 4. Create Database Record
        return ExecutionEvidence::create([
            'execution_id' => $execution->id,
            'file_path' => $path,
            'file_name' => $originalName,
            'file_size' => filesize($absolutePath),
            'mime_type' => mime_content_type($absolutePath) ?: 'application/octet-stream',
            'file_hash' => $hash,
            'uploaded_by' => $userId,
            'uploaded_at' => now(),
            'is_replacement' => false,
        ]);
    }

    /**
     * Revoke an evidence.
     * WORM Compliance: We do NOT delete the file from storage. We only mark it as revoked in DB.
     * 
     * @param ExecutionEvidence $evidence
     * @param int $userId
     * @param string $reason
     * @return bool
     */
    public function revokeEvidence(ExecutionEvidence $evidence, int $userId, string $reason): bool
    {
        // Validación: No se puede revocar lo que ya está revocado
        if ($evidence->revoked_at) {
            return false;
        }

        // 1. Mover archivo a carpeta _TRASH local (mismo nivel)
        try {
            $currentPath = $evidence->file_path;
            $directory = dirname($currentPath);
            $filename = basename($currentPath);
            
            // Nueva ruta: .../FECHA/_TRASH/filename
            $trashDir = $directory . '/_TRASH';
            
            // Asegurar que exista el directorio _TRASH (Google Drive adapter suele necesitarlo o lo maneja, mejor asegurar)
            // En algunos adapters makeDirectory es idempotente.
            if (!Storage::disk($this->disk)->exists($trashDir)) {
                Storage::disk($this->disk)->makeDirectory($trashDir);
            }

            $newPath = $trashDir . '/' . $filename;

            // Verificar si ya existe en trash (caso raro de doble borrado con mismo nombre)
            if (Storage::disk($this->disk)->exists($newPath)) {
                $timestamp = now()->format('YmdHis');
                $newPath = $trashDir . '/' . pathinfo($filename, PATHINFO_FILENAME) . "_DEL_{$timestamp}." . pathinfo($filename, PATHINFO_EXTENSION);
            }

            // Mover físicamente en Drive
            Storage::disk($this->disk)->move($currentPath, $newPath);

            // Actualizar path en BD para mantener referencia válida
            $evidence->file_path = $newPath;
            $evidence->save();

        } catch (\Exception $e) {
            // Si falla el movimiento (ej. permisos), logueamos pero continuamos con la revocación lógica
            // para no bloquear al usuario.
            \Illuminate\Support\Facades\Log::error("Error moving file to trash: " . $e->getMessage());
        }

        // 2. Revocación Lógica
        $evidence->update([
            'revoked_at' => now(),
            'revoked_by' => $userId,
            'revoke_reason' => $reason
        ]);

        return true;
    }

    /**
     * Replace an evidence (Revoke old + Upload new).
     * 
     * @param ExecutionEvidence $oldEvidence
     * @param UploadedFile $newFile
     * @param int $userId
     * @param string $reason
     * @return ExecutionEvidence
     */
    public function replaceEvidence(ExecutionEvidence $oldEvidence, UploadedFile $newFile, int $userId, string $reason): ExecutionEvidence
    {
        return DB::transaction(function () use ($oldEvidence, $newFile, $userId, $reason) {
            // 1. Revoke old
            $this->revokeEvidence($oldEvidence, $userId, "Reemplazado: " . $reason);

            // 2. Upload new
            $newEvidence = $this->storeEvidence($oldEvidence->execution, $newFile, $userId);
            
            // 3. Mark as replacement
            $newEvidence->is_replacement = true;
            $newEvidence->save();

            return $newEvidence;
        });
    }
    
    /**
     * Get URL for evidence (checking permissions/revocation if needed).
     */
    public function getUrl(ExecutionEvidence $evidence): string
    {
        return Storage::disk($this->disk)->url($evidence->file_path);
    }
}
