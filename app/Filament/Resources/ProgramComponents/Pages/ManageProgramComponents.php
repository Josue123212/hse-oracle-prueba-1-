<?php

namespace App\Filament\Resources\ProgramComponents\Pages;

use App\Filament\Resources\ProgramComponents\ProgramComponentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProgramComponents extends ManageRecords
{
    protected static string $resource = ProgramComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
