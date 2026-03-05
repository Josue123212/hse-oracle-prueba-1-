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
use Illuminate\Database\Eloquent\Model;

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
                        ->options(\App\Models\Program::pluck('nombre', 'id'))
                        ->default(function ($record) {
                            // Si es una Actividad (padre)
                            if ($record instanceof \App\Models\Activity) {
                                return $record->component?->program_id;
                            }
                            // Si es un Satélite (hijo)
                            if ($record instanceof \Illuminate\Database\Eloquent\Model) {
                                return $record->program_id ?? $record->activity?->component?->program_id;
                            }
                            return null;
                        })
                        ->formatStateUsing(function ($state, $record) {
                            if ($state) return $state;
                            
                            if ($record instanceof \App\Models\Activity) {
                                return $record->component?->program_id;
                            }
                            
                            if ($record instanceof \Illuminate\Database\Eloquent\Model) {
                                return $record->program_id ?? $record->activity?->component?->program_id;
                            }
                            return null;
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required()
                        ->dehydrated(false) // No guardar este campo en la BD, solo sirve para filtrar
                        ->afterStateUpdated(fn (callable $set) => $set('program_component_id', null)),
                    
                    Select::make('program_component_id')
                        ->label('Componente / Elemento')
                        ->options(function (Get $get, ?Model $record) {
                            // Intentar obtener del estado del formulario (cuando el usuario cambia el programa)
                            $programId = $get('program_id');
                            
                            // Si no hay en el estado, intentar deducir del registro actual (carga inicial)
                            if (!$programId && $record) {
                                if ($record instanceof \App\Models\Activity) {
                                    $programId = $record->component?->program_id;
                                } elseif ($record instanceof \Illuminate\Database\Eloquent\Model) {
                                    $programId = $record->program_id ?? $record->activity?->component?->program_id;
                                }
                            }
                            
                            if (!$programId) return [];
                            
                            return \App\Models\ProgramComponent::where('program_id', $programId)
                                ->doesntHave('children')
                                ->with('parent')
                                ->get()
                                ->pluck('full_name', 'id');
                        })
                        ->default(fn ($record) => $record?->program_component_id ?? $record?->activity?->program_component_id)
                        ->formatStateUsing(function ($state, $record) {
                            if ($state) return $state;
                            
                            if ($record instanceof \App\Models\Activity) {
                                return $record->program_component_id;
                            }
                            
                            if ($record instanceof \Illuminate\Database\Eloquent\Model) {
                                return $record->program_component_id ?? $record->activity?->program_component_id;
                            }
                            return null;
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
                        ->default(fn ($record) => $record instanceof \App\Models\Activity ? $record->fecha_inicio : ($record?->fecha_programada ?? $record?->activity?->fecha_inicio))
                        ->formatStateUsing(function ($state, $record) {
                            if ($state) return $state;
                            if ($record instanceof \App\Models\Activity) return $record->fecha_inicio;
                            return $record?->fecha_programada ?? $record?->activity?->fecha_inicio;
                        })
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
                        ->required()
                        ->dehydrated(),

                    TextInput::make('veces_al_anio')
                        ->label('Veces al Año')
                        ->numeric()
                        ->default(fn ($record) => $record?->activity?->veces_al_anio ?? 1)
                        ->formatStateUsing(fn ($state, $record) => $state ?? $record?->activity?->veces_al_anio ?? 1)
                        ->readOnly()
                        ->dehydrated(),
                    
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
