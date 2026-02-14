<?php

namespace App\Filament\Resources\ReportTypes\Schemas;

use Filament\Schemas\Schema;

class ReportTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(100),
                \Filament\Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->columnSpanFull(),
                \Filament\Forms\Components\Select::make('frecuencia_requerida')
                    ->label('Frecuencia requerida')
                    ->options([
                        'diario' => 'Diario',
                        'semanal' => 'Semanal',
                        'mensual' => 'Mensual',
                    ])
                    ->default('diario')
                    ->required()
                    ->native(false),
                \Filament\Forms\Components\Toggle::make('es_obligatorio')
                    ->label('¿Es obligatorio?')
                    ->default(true)
                    ->onColor('success'),
            ]);
    }
}
