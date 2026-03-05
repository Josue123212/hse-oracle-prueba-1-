<?php

namespace App\Filament\Resources\Trainings\Schemas;

use App\Filament\Resources\Activities\Schemas\BaseActivityForm;
use Filament\Forms\Components\TextInput;

class TrainingForm extends BaseActivityForm
{
    public static function getSpecificFields(): array
    {
        return [
            TextInput::make('nombre')
                ->label('Nombre de la Capacitación')
                ->required()
                ->maxLength(255),
        ];
    }
}
