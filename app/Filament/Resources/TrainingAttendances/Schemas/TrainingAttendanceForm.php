<?php

namespace App\Filament\Resources\TrainingAttendances\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
class TrainingAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('training_id')
                    ->label('Capacitación')
                    ->relationship('training', 'titulo')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('user_id')
                    ->label('Participante')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                DateTimePicker::make('fecha_inicio')
                    ->label('Fecha de inicio')
                    ->seconds(false),

                DateTimePicker::make('fecha_fin')
                    ->label('Fecha de fin')
                    ->seconds(false),

                ViewField::make('firma_digital')
                    ->label('Firma digital')
                    ->view('filament.forms.components.signature-pad')
                    ->required()
                    ->helperText('Firme directamente en el recuadro superior.'),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_progreso' => 'En progreso',
                        'completado' => 'Completado',
                        'reprobado' => 'Reprobado',
                    ])
                    ->default('pendiente')
                    ->required()
                    ->native(false),

                Toggle::make('aprobado')
                    ->label('Aprobado')
                    ->default(false),

                TextInput::make('certificado_url')
                    ->label('URL del certificado')
                    ->maxLength(500),
            ]);
    }
}
