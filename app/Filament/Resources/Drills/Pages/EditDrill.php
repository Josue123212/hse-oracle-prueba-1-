<?php

namespace App\Filament\Resources\Drills\Pages;

use App\Filament\Resources\Drills\DrillResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDrill extends EditRecord
{
    protected static string $resource = DrillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
