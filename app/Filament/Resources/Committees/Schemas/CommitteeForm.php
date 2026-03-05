<?php

namespace App\Filament\Resources\Committees\Schemas;

use App\Filament\Resources\Activities\Schemas\BaseActivityForm;
use Filament\Forms\Components\TextInput;

class CommitteeForm extends BaseActivityForm
{
    public static function getSpecificFields(): array
    {
        return [
            TextInput::make('nombre')
                ->required()
                ->maxLength(255)
                ->label('Nombre de la Reunión'),

            TextInput::make('tema_principal')
                ->maxLength(255)
                ->label('Tema Principal'),
        ];
    }
}
