<?php

namespace App\Filament\Resources\ActivityExecutions\Pages;

use App\Filament\Resources\ActivityExecutions\ActivityExecutionResource;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ActivityExecution;
use App\Models\Activity;
use App\Services\DrivePathGenerator;
use App\Filament\Traits\HasEvidencePreview;

class ManageActivityExecutions extends ManageRecords
{
    use HasEvidencePreview;

    protected static string $resource = ActivityExecutionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $newEvidences = $data['new_evidencia'] ?? [];
                    $finalPaths = [];
                    
                    if (!empty($newEvidences)) {
                        $dummyRecord = new ActivityExecution();
                        $dummyRecord->activity_id = $data['activity_id'];
                        $dummyRecord->setRelation('activity', Activity::find($data['activity_id']));
                        $dummyRecord->fecha_programada = $data['fecha_programada'] ?? null;
                        $dummyRecord->fecha_ejecucion_real = $data['fecha_ejecucion_real'] ?? null;
                        
                        $targetDir = DrivePathGenerator::generate($dummyRecord);
                        
                        if (!Storage::disk('google')->exists($targetDir)) {
                             Storage::disk('google')->makeDirectory($targetDir);
                        }
            
                        foreach ($newEvidences as $tempPath) {
                            if (Storage::disk('public')->exists($tempPath)) {
                                $fileName = basename($tempPath);
                                $targetPath = trim($targetDir, '/') . '/' . $fileName;
                                
                                Storage::disk('google')->put($targetPath, Storage::disk('public')->get($tempPath));
                                if (Storage::disk('google')->exists($targetPath)) {
                                    $finalPaths[] = $targetPath;
                                    Storage::disk('public')->delete($tempPath);
                                }
                            }
                        }
                    }
                    
                    $data['evidencia'] = $finalPaths;
                    unset($data['new_evidencia']);
                    unset($data['existing_evidences']);
                    
                    return $data;
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas'),
            'programadas' => Tab::make('Programadas')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('fecha_programada')),
            'eventuales' => Tab::make('Eventuales')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('fecha_programada')),
        ];
    }
}
