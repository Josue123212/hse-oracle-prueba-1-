<?php

namespace App\Filament\Resources\TrainingAttendances\Pages;

use App\Filament\Resources\TrainingAttendances\TrainingAttendanceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\DeleteAction;

class ViewTrainingAttendance extends ViewRecord
{
    protected static string $resource = TrainingAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
