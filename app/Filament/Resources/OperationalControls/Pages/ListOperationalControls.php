<?php

namespace App\Filament\Resources\OperationalControls\Pages;

use App\Filament\Resources\OperationalControls\OperationalControlResource;
use App\Filament\Resources\OperationalControls\Widgets\OperationalControlsForToday;
use App\Filament\Resources\OperationalControls\Widgets\EventualOperationalControls;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOperationalControls extends ListRecords
{
    protected static string $resource = OperationalControlResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            OperationalControlsForToday::class,
            EventualOperationalControls::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
