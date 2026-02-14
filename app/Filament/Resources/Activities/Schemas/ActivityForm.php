<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('codigo')
                ->label('Código de Actividad')
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder('ej. ACT-001'),

            TextInput::make('nombre')
                ->label('Nombre de la Actividad')
                ->required()
                ->placeholder('ej. Charla de 5 minutos'),

            Select::make('frecuencia')
                ->label('Frecuencia de Ejecución')
                ->options([
                    'diario' => 'Diario',
                    'semanal' => 'Semanal',
                    'mensual' => 'Mensual',
                    'anual' => 'Anual',
                    'trimestral' => 'Trimestral',
                    'cuando_requiera' => 'Cuando se requiera',
                ])
                ->required()
                ->native(false),

            Select::make('location_id')
                ->label('Ubicación / Sede')
                ->relationship('location', 'nombre')
                ->required()
                ->searchable()
                ->preload()
                ->key('location_select')
                ->createOptionForm(fn (Schema $schema) => \App\Filament\Resources\Locations\Schemas\LocationForm::configure($schema)->getComponents()),
        ]);
    }
}