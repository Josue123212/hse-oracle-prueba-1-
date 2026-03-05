<?php

namespace App\Filament\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\DrivePathGenerator;

trait HandlesActivityExecution
{
    protected function moveEvidenceFilesToDrive($record, $filePaths): array
    {
        if (empty($filePaths)) {
            return [];
        }

        if (is_string($filePaths)) {
            $filePaths = [$filePaths];
        }

        $finalDrivePaths = [];
        $localDisk = Storage::disk('public');
        $googleDisk = Storage::disk('google');

        foreach ($filePaths as $localPath) {
            if ($localDisk->exists($localPath)) {
                // Generate path based on the record (Activity or ActivityExecution)
                $targetDirectory = DrivePathGenerator::generate($record);
                $fileName = basename($localPath);
                $targetPath = trim($targetDirectory, '/') . '/' . $fileName;
                
                try {
                    if (!$googleDisk->exists($targetDirectory)) {
                        $googleDisk->makeDirectory($targetDirectory);
                    }
                    
                    $fileContents = $localDisk->get($localPath);
                    $googleDisk->put($targetPath, $fileContents);
                    
                    if ($googleDisk->exists($targetPath)) {
                        $finalDrivePaths[] = $targetPath;
                        $localDisk->delete($localPath);
                    }
                } catch (\Exception $e) {
                    Log::error("Error moviendo archivo a Drive: " . $e->getMessage());
                }
            }
        }
        
        return $finalDrivePaths;
    }
}
