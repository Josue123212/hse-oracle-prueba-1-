<?php

namespace App\Filament\Resources\Inspections\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use Filament\Schemas\Components\Utilities\Get;
use App\Enums\ActivityState;

class InspectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Programación de la Inspección')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('program_id')
                                    ->label('Programa QHSE')
                                    ->relationship('program', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                
                                Select::make('responsable_id')
                                    ->label('Responsable / Inspector')
                                    ->relationship('responsable', 'name')
                                    ->searchable()
                                    ->preload(),

                                DatePicker::make('fecha_programada')
                                    ->label('Fecha Programada')
                                    ->required()
                                    ->live(),

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
                                    ->formatStateUsing(fn ($state, $record) => $state ?? $record?->activity?->frecuencia)
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

                                Select::make('estado')
                                    ->label('Estado Actual')
                                    ->options(ActivityState::class)
                                    ->default(ActivityState::PROGRAMADO->value)
                                    ->required()
                                    ->live(),
                            ]),
                        
                        Placeholder::make('fechas_programadas_visual')
                            ->label('Fechas del año')
                            ->content(function (Get $get) {
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
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Ejecución y Resultados')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Select::make('resultado')
                                    ->label('Resultado')
                                    ->options([
                                        'aprobado' => 'Aprobado',
                                        'rechazado' => 'Rechazado / Observado',
                                        'pendiente' => 'Pendiente de Revisión',
                                    ])
                                    ->visible(fn (Get $get) => $get('estado') === ActivityState::EJECUTADO->value),
                            ]),
                        
                        Textarea::make('observaciones')
                            ->label('Observaciones / Hallazgos')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
