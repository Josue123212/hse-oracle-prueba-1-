<?php

namespace App\Filament\Resources\Incidents\Pages;

use App\Filament\Resources\Incidents\IncidentResource;
use App\Filament\Resources\Incidents\Widgets\OpenIncidents;
use App\Filament\Resources\Incidents\Widgets\EventualIncidents;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIncidents extends ListRecords
{
    protected static string $resource = IncidentResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            OpenIncidents::class,
            EventualIncidents::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
