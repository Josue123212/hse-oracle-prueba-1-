<?php

namespace App\Filament\Resources\Incidents\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Activity;

class IncidentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Incidente')
                    ->description('Registre los detalles del incidente.')
                    ->schema([
                        Select::make('program_id')
                            ->relationship('program', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-briefcase')
                            ->label('Programa Asociado')
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('activity_id', null);
                            }),

                        Select::make('activity_id')
                            ->label('Actividad Relacionada')
                            ->options(fn (Get $get) => Activity::where('program_id', $get('program_id'))->pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $activity = Activity::find($state);
                                    if ($activity) {
                                        $set('titulo', $activity->nombre);
                                    }
                                } else {
                                    $set('titulo', null);
                                }
                            }),

                        TextInput::make('titulo')
                            ->required()
                            ->maxLength(255)
                            ->label('Título del Incidente')
                            ->placeholder('Ej. Caída de material'),

                        Grid::make(2)
                            ->schema([
                                DatePicker::make('fecha_ocurrencia')
                                    ->required()
                                    ->label('Fecha de Ocurrencia')
                                    ->prefixIcon('heroicon-o-calendar-days')
                                    ->maxDate(now()),

                                Select::make('estado')
                                    ->options([
                                        'programado' => 'Programado',
                                        'ejecutado' => 'Ejecutado',
                                        'cancelado' => 'Cancelado',
                                    ])
                                    ->required()
                                    ->default('programado')
                                    ->label('Estado')
                                    ->native(false),
                            ]),

                        Textarea::make('descripcion')
                            ->label('Descripción / Observaciones')
                            ->placeholder('Detalles del evento...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
