<?php

namespace App\Filament\Resources\PositionTypes\Pages;

use App\Filament\Resources\PositionTypes\PositionTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPositionTypes extends ListRecords
{
    protected static string $resource = PositionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Tipo de Cargo'),
        ];
    }
}
