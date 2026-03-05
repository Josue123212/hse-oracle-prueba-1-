<?php

namespace App\Filament\Resources\Documentations\Schemas;

use App\Filament\Resources\Activities\Schemas\BaseActivityForm;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

class DocumentationForm extends BaseActivityForm
{
    public static function getSpecificFields(): array
    {
        return [
            Grid::make(2)->schema([
                Select::make('activity_id')
                    ->relationship('activity', 'nombre')
                    ->label('Actividad Relacionada')
                    ->searchable()
                    ->preload(),

                TextInput::make('titulo')
                    ->required()
                    ->maxLength(255)
                    ->label('Título'),

                Select::make('tipo_documento')
                    ->options([
                        'procedimiento' => 'Procedimiento',
                        'manual' => 'Manual',
                        'politica' => 'Política',
                        'formato' => 'Formato',
                        'otro' => 'Otro',
                    ])
                    ->required()
                    ->default('otro')
                    ->label('Tipo de Documento'),

                Hidden::make('version')
                    ->default('1.0'),
            ]),
        ];
    }
}
