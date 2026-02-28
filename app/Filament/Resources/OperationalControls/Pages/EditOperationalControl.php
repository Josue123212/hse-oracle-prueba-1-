<?php

namespace App\Filament\Resources\OperationalControls\Pages;

use App\Filament\Resources\OperationalControls\OperationalControlResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOperationalControl extends EditRecord
{
    protected static string $resource = OperationalControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
