<?php

namespace App\Filament\Resources\Inspections\Widgets;

use App\Filament\Widgets\BaseScheduledActivityWidget;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;

class InspectionsForToday extends BaseScheduledActivityWidget
{
    protected function getActivityType(): string
    {
        return 'inspeccion';
    }

    protected function getHeadingTitle(): string
    {
        return 'Inspecciones Programadas para Hoy';
    }

    protected function getActivityLabel(): string
    {
        return 'Inspección';
    }

    protected function getIniciaFormDetails(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('data.location_id')
                        ->label('Ubicación')
                        ->options(\App\Models\Location::pluck('nombre', 'id'))
                        ->searchable()
                        ->preload(),
                    Textarea::make('data.observaciones')
                        ->label('Observaciones Adicionales'),
                ]),
        ];
    }
}
