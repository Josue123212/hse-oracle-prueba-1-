<?php

namespace App\Filament\Resources\Inspections\Schemas;

use App\Filament\Resources\Activities\Schemas\BaseActivityForm;
use Filament\Forms\Components\TextInput;

class InspectionForm extends BaseActivityForm
{
    public static function getSpecificFields(): array
    {
        return [
            TextInput::make('nombre')
                ->label('Nombre de la Inspección')
                ->required()
                ->maxLength(255),
        ];
    }
}
