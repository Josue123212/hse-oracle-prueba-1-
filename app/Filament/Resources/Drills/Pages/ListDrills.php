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
            \App\Filament\Resources\Drills\Widgets\DrillStatsOverview::class,
            \App\Filament\Resources\Drills\Widgets\DrillCalendarWidget::class,
            EventualDrills::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Simulacro')
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $service = new \App\Services\ActivityService();
                    return $service->createWithType($data, 'simulacro');
                }),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }
}
