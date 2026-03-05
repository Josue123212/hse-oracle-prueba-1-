<?php

namespace App\Filament\Resources\Incidents\Schemas;

use App\Filament\Resources\Activities\Schemas\BaseActivityForm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;

class IncidentForm extends BaseActivityForm
{
    public static function getSpecificFields(): array
    {
        return [
            TextInput::make('titulo')
                ->required()
                ->maxLength(255)
                ->label('Título del Incidente')
                ->placeholder('Ej. Caída de material'),

            DatePicker::make('fecha_ocurrencia')
                ->required()
                ->label('Fecha de Ocurrencia')
                ->prefixIcon('heroicon-o-calendar-days')
                ->maxDate(now()),
        ];
    }
}
