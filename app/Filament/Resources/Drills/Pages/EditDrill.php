<?php

namespace App\Filament\Resources\Drills\Pages;

use App\Filament\Resources\Drills\DrillResource;
use App\Services\ActivityService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDrill extends EditRecord
{
    protected static string $resource = DrillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = new ActivityService();
        return $service->updateWithType($record, $data);
    }
}
