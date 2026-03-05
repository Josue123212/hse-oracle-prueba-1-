<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Log;

abstract class BaseActivityForm
{
    abstract public static function getSpecificFields(): array;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Programación')
                    ->schema(array_merge(
                        static::getCommonFields(),
                        static::getSpecificFields(),
                        static::getFrequencyFields()
                    )),
                
                Section::make('Cronograma')
                    ->schema([
                        Placeholder::make('fechas_programadas_visual')
                            ->label('Fechas del año')
                            ->content(fn (Get $get) => static::renderSchedule($get))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function getResponsibleField(): Field
    {
        return Select::make('responsable_id')
            ->label('Cargo Responsable')
            ->relationship('responsable', 'nombre')
            ->searchable()
            ->preload();
    }

    protected static function getCommonFields(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('program_id')
                        ->label('Programa QHSE')
                        ->relationship('program', 'nombre')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),
                    
                    Select::make('program_component_id')
                        ->label('Componente / Elemento')
                        ->options(function (Get $get) {
                            $programId = $get('program_id');
                            if (!$programId) return [];
                            return \App\Models\ProgramComponent::where('program_id', $programId)
                                ->doesntHave('children')
                                ->with('parent')
                                ->get()
                                ->pluck('full_name', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled(fn (Get $get) => !$get('program_id')),

                    static::getResponsibleField(),

                    Select::make('location_id')
                        ->relationship('location', 'nombre')
                        ->label('Sede')
                        ->searchable()
                        ->preload()
                        ->required(),
                    
                    Select::make('responsable_delegado_id')
                        ->label('Cargo Responsable Delegado')
                        ->relationship('responsableDelegado', 'nombre')
                        ->searchable()
                        ->preload(),

                    TextInput::make('apoyo')
                        ->label('Apoyo'),

                    DatePicker::make('fecha_programada')
                        ->label('Fecha Inicial')
                        ->required(fn (Get $get) => $get('frecuencia') !== 'eventual')
                        ->visible(fn (Get $get) => $get('frecuencia') !== 'eventual')
                        ->live(),
                ]),
        ];
    }

    protected static function getFrequencyFields(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('frecuencia')
                        ->label('Frecuencia')
                        ->options([
                            'diario' => 'Diario',
                            'semanal' => 'Semanal',
                            'mensual' => 'Mensual',
                            'trimestral' => 'Trimestral',
                            'semestral' => 'Semestral',
                            'anual' => 'Anual',
                            'eventual' => 'Eventualmente',
                        ])
                        ->default(fn ($record) => $record?->activity?->frecuencia)
                        ->formatStateUsing(function ($state, $record) {
                            if (empty($state) && $record?->activity) {
                                return $record->activity->frecuencia;
                            }
                            return $state;
                        })
                        ->live()
                        ->afterStateUpdated(function ($set, ?string $state) {
                            $map = [
                                'diario' => 365,
                                'semanal' => 52,
                                'mensual' => 12,
                                'trimestral' => 4,
                                'semestral' => 2,
                                'anual' => 1,
                                'eventual' => 1,
                            ];
                            $set('veces_al_anio', $map[$state] ?? 1);
                            $set('ejecuciones_realizadas', 0);
                        })
                        ->required(),

                    TextInput::make('veces_al_anio')
                        ->label('Veces al Año')
                        ->numeric()
                        ->default(fn ($record) => $record?->activity?->veces_al_anio ?? 1)
                        ->formatStateUsing(fn ($state, $record) => $state ?? $record?->activity?->veces_al_anio ?? 1)
                        ->readOnly(),
                    
                    TextInput::make('detalle_frecuencia')
                        ->label('Detalle de Eventualidad')
                        ->placeholder('Especifique la razón (ej. Cuando ocurra, Personal nuevo)')
                        ->default(fn ($record) => $record?->activity?->detalle_frecuencia)
                        ->formatStateUsing(fn ($state, $record) => $state ?? $record?->activity?->detalle_frecuencia)
                        ->required(fn (Get $get) => $get('frecuencia') === 'eventual')
                        ->visible(fn (Get $get) => $get('frecuencia') === 'eventual')
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected static function renderSchedule(Get $get): HtmlString | string
    {
        $fechaInicio = $get('fecha_programada');
        $frecuencia = $get('frecuencia');
        $vecesAlAnio = (int) $get('veces_al_anio');

        if (!$fechaInicio || !$frecuencia) {
            return new HtmlString('<span class="text-gray-500 italic">Seleccione fecha programada y frecuencia para ver el cronograma.</span>');
        }

        if ($frecuencia === 'eventual' || $vecesAlAnio === 0) {
            $detalle = $get('detalle_frecuencia') ? ': ' . htmlspecialchars($get('detalle_frecuencia')) : '';
            if ($fechaInicio) {
                try {
                    $date = Carbon::parse($fechaInicio)->format('d/m/Y');
                    return new HtmlString('<span class="text-gray-500 italic">Eventualmente' . $detalle . ' - <strong>Ejecutada el: ' . $date . '</strong></span>');
                } catch (\Exception $e) {}
            }
            return new HtmlString('<span class="text-gray-500 italic">Eventualmente' . $detalle . '</span>');
        }

        try {
            $date = Carbon::parse($fechaInicio);
        } catch (\Exception $e) {
            return 'Fecha inválida';
        }

        $fechas = [];
        $limit = match($frecuencia) {
            'diario' => 10,
            'semanal' => 12,
            'mensual' => 12,
            'trimestral' => 4,
            'semestral' => 2,
            'anual' => 1,
            'eventual' => 1,
            default => 1
        };

        for ($i = 0; $i < $limit; $i++) {
            $fechas[] = $date->format('d/m/Y');
            match($frecuencia) {
                'diario' => $date->addDay(),
                'semanal' => $date->addWeek(),
                'mensual' => $date->addMonth(),
                'trimestral' => $date->addMonths(3),
                'semestral' => $date->addMonths(6),
                'anual' => $date->addYear(),
                default => null,
            };
        }

        $html = '<div class="grid grid-cols-2 md:grid-cols-4 gap-2">';
        foreach ($fechas as $f) {
            $html .= "<div class='bg-gray-100 dark:bg-gray-800 p-2 rounded text-center text-sm'>{$f}</div>";
        }
        if ($frecuencia === 'diario' && $limit === 10) {
            $html .= "<div class='bg-gray-100 dark:bg-gray-800 p-2 rounded text-center text-sm'>...</div>";
        }
        $html .= '</div>';

        return new HtmlString($html);
    }
}
