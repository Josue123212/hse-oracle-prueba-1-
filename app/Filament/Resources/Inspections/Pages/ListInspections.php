<?php

namespace App\Filament\Resources\Inspections\Pages;

use App\Filament\Resources\Inspections\InspectionResource;
use App\Filament\Resources\Inspections\Widgets\InspectionStatsOverview;
use App\Filament\Resources\Inspections\Widgets\InspectionsForToday;
use App\Filament\Resources\Inspections\Widgets\EventualInspections;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Traits\HasEvidencePreview;

class ListInspections extends ListRecords
{
    use HasEvidencePreview;

    protected static string $resource = InspectionResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            InspectionStatsOverview::class,
            InspectionsForToday::class,
            EventualInspections::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Inspección')
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $service = new \App\Services\ActivityService();
                    return $service->createWithType($data, 'inspeccion');
                }),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }
}
