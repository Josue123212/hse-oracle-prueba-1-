<?php

namespace App\Filament\Resources\Repositories\Schemas;

use Filament\Schemas\Schema;

class RepositoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required(),
                \Filament\Forms\Components\TextInput::make('url')
                    ->label('Ruta/URL base')
                    ->maxLength(500),
                \Filament\Forms\Components\Select::make('estado')
                    ->options([
                        'vacio' => 'Vacío',
                        'lleno' => 'Lleno',
                        'pendiente' => 'Pendiente',
                    ])
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('fecha_creacion')
                    ->label('Fecha creación'),
                \Filament\Forms\Components\Toggle::make('esta_llena')
                    ->label('Está llena'),
                \Filament\Forms\Components\DateTimePicker::make('ultima_verificacion')
                    ->label('Última verificación'),
                \Filament\Forms\Components\Select::make('user_id')
                    ->label('Responsable')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Forms\Components\Select::make('repositoriable_type')
                    ->label('Tipo asociado')
                    ->options([
                        \App\Models\Inspection::class => 'Inspección',
                        \App\Models\Program::class => 'Programa',
                        \App\Models\Activity::class => 'Actividad',
                    ])
                    ->required(),
                \Filament\Forms\Components\TextInput::make('repositoriable_id')
                    ->label('ID asociado')
                    ->numeric()
                    ->required(),
            ]);
    }
}
