<?php

namespace App\Filament\Resources\Audits\Widgets;

use App\Filament\Widgets\BaseEventualActivityWidget;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;

class EventualAudits extends BaseEventualActivityWidget
{
    protected function getActivityType(): string
    {
        return 'auditoria';
    }

    protected function getHeadingTitle(): string
    {
        return 'Actividades Eventuales - Auditorías';
    }

    protected function getActivityLabel(): string
    {
        return 'Auditoría';
    }

    protected function getIniciaFormDetails(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('data.auditor_id')
                        ->label('Supervisor')
                        ->options(\App\Models\Supervisor::pluck('nombre', 'id'))
                        ->searchable()
                        ->preload(),
                    Textarea::make('data.hallazgos')
                        ->label('Hallazgos'),
                ]),
        ];
    }
}
