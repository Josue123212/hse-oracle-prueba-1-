<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Activities\Widgets\ActivityStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Traits\HasEvidencePreview;

class ListActivities extends ListRecords
{
    use HasEvidencePreview;

    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ActivityStatsOverview::class,
        ];
    }
}
