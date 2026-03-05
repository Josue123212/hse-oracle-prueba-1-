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
            \App\Filament\Resources\Incidents\Widgets\IncidentStatsOverview::class,
            OpenIncidents::class,
            EventualIncidents::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Incidente')
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $service = new \App\Services\ActivityService();
                    return $service->createWithType($data, 'incidente');
                }),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }
}
