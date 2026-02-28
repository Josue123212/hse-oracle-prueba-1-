<?php

namespace App\Filament\Resources\Inspections\Pages;

use App\Filament\Resources\Inspections\InspectionResource;
use App\Filament\Resources\Inspections\Widgets\InspectionStatsOverview;
use App\Filament\Resources\Inspections\Widgets\InspectionsForToday;
use App\Filament\Resources\Inspections\Widgets\EventualInspections;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInspections extends ListRecords
{
    protected static string $resource = InspectionResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            InspectionsForToday::class,
            EventualInspections::class,
            InspectionStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
