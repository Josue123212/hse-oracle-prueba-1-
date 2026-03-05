<?php

namespace App\Filament\Resources\Audits\Schemas;

use App\Filament\Resources\Activities\Schemas\BaseActivityForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Field;

class AuditForm extends BaseActivityForm
{
    public static function getSpecificFields(): array
    {
        return [
            TextInput::make('nombre')
                ->label('Nombre de la Auditoría')
                ->required()
                ->maxLength(255),
            
            Textarea::make('descripcion')
                ->label('Descripción')
                ->rows(2)
                ->columnSpanFull(),
        ];
    }

    protected static function getResponsibleField(): Field
    {
        return Select::make('auditor_id')
            ->label('Supervisor Auditor')
            ->relationship('auditor', 'nombre')
            ->searchable()
            ->preload();
    }
}
