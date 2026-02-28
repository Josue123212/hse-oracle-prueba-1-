<?php

namespace App\Filament\Resources\Drills\Pages;

use App\Filament\Resources\Drills\DrillResource;
use App\Filament\Resources\Drills\Widgets\DrillsForToday;
use App\Filament\Resources\Drills\Widgets\EventualDrills;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDrills extends ListRecords
{
    protected static string $resource = DrillResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            DrillsForToday::class,
            EventualDrills::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
