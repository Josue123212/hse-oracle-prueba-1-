<?php

namespace App\Filament\Resources\TrainingAttendances\Pages;

use App\Filament\Resources\TrainingAttendances\TrainingAttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingAttendances extends ListRecords
{
    protected static string $resource = TrainingAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Asistencia'),
        ];
    }
}
