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
                                $set('program_component_id', null);
                            }),

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

                        Select::make('responsable_id')
                            ->relationship('responsable', 'nombre')
                            ->searchable()
                            ->preload()
                            ->label('Cargo Responsable'),

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
                                    ->maxDate(now())
                                    ->live(),
                            ]),

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
                            ->default('eventual')
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
                            }),

                        TextInput::make('veces_al_anio')
                            ->label('Veces al Año')
                            ->numeric()
                            ->readOnly()
                            ->default(1)
                            ->hidden(),

                        TextInput::make('ejecuciones_realizadas')
                            ->label('Ejecuciones Realizadas')
                            ->numeric()
                            ->readOnly()
                            ->default(0)
                            ->hidden(),

                        TextInput::make('detalle_frecuencia')
                            ->label('Detalle de Eventualidad')
                            ->placeholder('Especifique la razón')
                            ->required(fn (Get $get) => $get('frecuencia') === 'eventual')
                            ->visible(fn (Get $get) => $get('frecuencia') === 'eventual')
                            ->columnSpanFull(),

                        Placeholder::make('fechas_programadas_visual')
                            ->label('Fechas del año')
                            ->content(function (Get $get) {
                                $fechaInicio = $get('fecha_ocurrencia');
                                $frecuencia = $get('frecuencia');
                                $vecesAlAnio = (int) $get('veces_al_anio');
        
                                if (!$fechaInicio || !$frecuencia) {
                                    return new HtmlString('<span class="text-gray-500 italic">Seleccione fecha y frecuencia para ver el cronograma.</span>');
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
                            })
                            ->columnSpanFull(),

                        Textarea::make('descripcion')
                            ->label('Descripción / Observaciones')
                            ->placeholder('Detalles del evento...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
