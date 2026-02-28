<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Programa')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(150)
                                    ->label('Nombre del Programa'),

                                Select::make('parent_id')
                                    ->relationship('parent', 'nombre')
                                    ->label('Programa Padre (Opcional)')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Seleccione si este es un sub-programa'),

                                Select::make('anio')
                                    ->label('Año')
                                    ->options(array_combine(range(date('Y'), date('Y') + 5), range(date('Y'), date('Y') + 5)))
                                    ->default(date('Y'))
                                    ->required(),

                                Select::make('estado')
                                    ->options([
                                        'borrador' => 'Borrador',
                                        'aprobado' => 'Aprobado',
                                        'cerrado' => 'Cerrado',
                                    ])
                                    ->default('borrador')
                                    ->required(),
                                
                                Select::make('supervisor_id')
                                    ->relationship('supervisor', 'nombre')
                                    ->label('Supervisor Responsable')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        
                        Textarea::make('descripcion')
                            ->label('Descripción / Objetivos')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
