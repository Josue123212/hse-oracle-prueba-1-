<?php

namespace App\Filament\Resources\Trainings\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('titulo')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(4),

                TextInput::make('url_video')
                    ->label('URL del video')
                    ->url(),

                TextInput::make('nota_minima')
                    ->label('Nota mínima')
                    ->numeric()
                    ->default(70)
                    ->required(),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'publicado' => 'Publicado',
                        'finalizada' => 'Finalizada',
                    ])
                    ->default('borrador')
                    ->required(),

                DatePicker::make('fecha_inicio')
                    ->label('Fecha de inicio'),

                DatePicker::make('fecha_fin')
                    ->label('Fecha de fin'),

                TextInput::make('duracion_horas')
                    ->label('Duración (horas)')
                    ->numeric(),
            ]);
    }
}
