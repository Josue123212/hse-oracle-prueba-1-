<?php

namespace App\Filament\Resources\OperationalControls\Schemas;

use App\Filament\Resources\Activities\Schemas\BaseActivityForm;
use Filament\Forms\Components\TextInput;

class OperationalControlForm extends BaseActivityForm
{
    public static function getSpecificFields(): array
    {
        return [
            TextInput::make('nombre_proceso')
                ->required()
                ->maxLength(255)
                ->label('Nombre del Proceso'),

            TextInput::make('parametro')
                ->required()
                ->maxLength(255)
                ->label('Parámetro'),

            TextInput::make('valor_esperado')
                ->required()
                ->maxLength(255)
                ->label('Valor Esperado'),
        ];
    }
}
