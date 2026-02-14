<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('report_type_id')
                    ->label('Tipo de reporte')
                    ->relationship('reportType', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                DatePicker::make('fecha')
                    ->label('Fecha')
                    ->required(),

                DateTimePicker::make('fecha_subida')
                    ->label('Fecha de subida')
                    ->seconds(false),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'subido' => 'Subido',
                        'vencido' => 'Vencido',
                    ])
                    ->default('pendiente')
                    ->required()
                    ->native(false),

                Toggle::make('archivo_detectado')
                    ->label('Archivo detectado')
                    ->default(false),

                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->columnSpanFull(),
            ]);
    }
}
