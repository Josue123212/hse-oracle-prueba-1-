<?php

namespace App\Filament\Resources\Audits\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class AuditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('activity_id')
                    ->label('Actividad')
                    ->relationship('activity', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('nombre')
                    ->label('Nombre de la auditoría')
                    ->required()
                    ->maxLength(255),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->columnSpanFull(),

                Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'interna' => 'Interna',
                        'externa' => 'Externa',
                        'verificacion' => 'Verificación',
                    ])
                    ->default('interna')
                    ->required()
                    ->native(false),

                TextInput::make('mes')
                    ->label('Mes')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(12),

                TextInput::make('anio')
                    ->label('Año')
                    ->numeric()
                    ->default(date('Y')),

                DatePicker::make('fecha')
                    ->label('Fecha'),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'programado' => 'Programado',
                        'ejecutado' => 'Ejecutado',
                        'no_ejecutado' => 'No ejecutado',
                        'parcial' => 'Parcial',
                    ])
                    ->default('programado')
                    ->required()
                    ->native(false),

                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->columnSpanFull(),
            ]);
    }
}
