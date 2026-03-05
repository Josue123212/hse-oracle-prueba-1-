<?php

namespace App\Filament\Resources\Drills\Schemas;

use App\Filament\Resources\Activities\Schemas\BaseActivityForm;
use Filament\Forms\Components\TextInput;

class DrillForm extends BaseActivityForm
{
    public static function getSpecificFields(): array
    {
        return [
            TextInput::make('nombre')
                ->required()
                ->maxLength(255)
                ->label('Nombre del Simulacro'),

            TextInput::make('escenario')
                ->maxLength(255)
                ->label('Escenario'),

            TextInput::make('participantes_count')
                ->numeric()
                ->default(0)
                ->required()
                ->label('Número de Participantes'),
        ];
    }
}
