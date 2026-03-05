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
            \App\Filament\Resources\OperationalControls\Widgets\OperationalControlStatsOverview::class,
            OperationalControlsForToday::class,
            EventualOperationalControls::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Control Operacional')
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $service = new \App\Services\ActivityService();
                    return $service->createWithType($data, 'control_operacional');
                }),
        ];
    }
}
