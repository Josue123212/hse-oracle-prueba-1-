<?php

namespace App\Filament\Resources\Documentations\Schemas;

use App\Enums\ActivityState;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

class DocumentationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('program_id')
                        ->relationship('program', 'nombre')
                        ->required()
                        ->preload()
                        ->label('Programa'),

                    Select::make('activity_id')
                        ->relationship('activity', 'titulo')
                        ->label('Actividad Relacionada')
                        ->searchable()
                        ->preload(),

                    TextInput::make('titulo')
                        ->required()
                        ->maxLength(255)
                        ->label('Título'),

                    Select::make('tipo_documento')
                        ->options([
                            'procedimiento' => 'Procedimiento',
                            'manual' => 'Manual',
                            'politica' => 'Política',
                            'formato' => 'Formato',
                            'otro' => 'Otro',
                        ])
                        ->required()
                        ->default('otro')
                        ->label('Tipo de Documento'),

                    Hidden::make('version')
                        ->default('1.0'),

                    DatePicker::make('fecha_programada')
                        ->label('Fecha Inicial')
                        ->required()
                        ->live()
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if (empty($state) && $record && $record->activity) {
                                $component->state($record->activity->fecha_inicio);
                            }
                        }),

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
                        ->live()
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if (empty($state) && $record && $record->activity) {
                                $component->state($record->activity->frecuencia);
                            } else if (empty($state) && $record) {
                                 $component->state($record->frecuencia ?? $record->activity?->frecuencia);
                            }
                        })
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
                        ->placeholder('Especifique la razón (ej. Cuando ocurra, Personal nuevo)')
                        ->required(fn (Get $get) => $get('frecuencia') === 'eventual')
                        ->visible(fn (Get $get) => $get('frecuencia') === 'eventual')
                        ->columnSpanFull(),

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

                    Select::make('responsable_id')
                        ->relationship('responsable', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Responsable'),
                    
                    TextInput::make('activity_estado_display')
                        ->label('Estado de la Actividad')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record?->activity?->estado ? ActivityState::tryFrom($record->activity->estado)?->getLabel() ?? $record->activity->estado : '-'),
                    
                    Select::make('estado')
                        ->label('Estado Documento')
                        ->options(ActivityState::class)
                        ->default(ActivityState::PROGRAMADO->value)
                        ->required()
                        ->live(),

                    Select::make('resultado')
                        ->label('Estado de Vigencia')
                        ->options([
                            'vigente' => 'Vigente',
                            'obsoleto' => 'Obsoleto',
                        ])
                        ->visible(fn (Get $get) => $get('estado') === ActivityState::EJECUTADO->value),

                    FileUpload::make('archivo_path')
                        ->label('Archivo')
                        ->directory('documentations')
                        ->columnSpanFull(),

                    Textarea::make('descripcion')
                        ->label('Descripción')
                        ->columnSpanFull(),
                ])
            ]);
    }
}
