<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Información de la Actividad')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('tipo')
                                ->label('Tipo de Actividad')
                                ->options([
                                    'general' => 'General',
                                    'auditoria' => 'Auditoría',
                                    'inspeccion' => 'Inspección',
                                    'capacitacion' => 'Capacitación',
                                    'simulacro' => 'Simulacro',
                                    'incidente' => 'Incidente',
                                    'comite' => 'Comité',
                                    'documentacion' => 'Documentación',
                                    'promocion' => 'Promoción',
                                    'control_operacional' => 'Control Operacional',
                                ])
                                ->default('general')
                                ->live()
                                ->required(),

                            TextInput::make('nombre')
                                ->label('Nombre de la Actividad')
                                ->required()
                                ->placeholder('ej. Charla de 5 minutos'),

                            Select::make('program_id')
                                ->label('Programa Asociado')
                                ->relationship('program', 'nombre')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('location_id')
                                ->relationship('location', 'nombre')
                                ->label('Sede')
                                ->searchable()
                                ->preload()
                                ->required(),
                            
                            Select::make('responsable_id')
                                ->label('Cargo Responsable')
                                ->relationship('responsable', 'nombre')
                                ->searchable()
                                ->preload(),

                            Select::make('responsable_delegado_id')
                                ->label('Cargo Responsable Delegado')
                                ->relationship('responsableDelegado', 'nombre')
                                ->searchable()
                                ->preload(),

                            TextInput::make('apoyo')
                                ->label('Apoyo')
                                ->placeholder('Ej. Supervisor QHSE'),
                            
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
                                    // Reset executions when frequency changes to avoid invalid state
                                    $set('ejecuciones_realizadas', 0);
                                })
                                ->required(),

                            TextInput::make('veces_al_anio')
                                ->label('Veces al Año')
                                ->numeric()
                                ->default(1)
                                ->readOnly()
                                ->required()
                                ->visible(fn (Get $get) => $get('frecuencia') !== 'eventual'),

                            Select::make('ejecuciones_realizadas')
                                ->label('Progreso (Ejecuciones)')
                                ->options(function (Get $get) {
                                    $max = (int) ($get('veces_al_anio') ?? 1);
                                    $options = [];
                                    for ($i = 0; $i <= $max; $i++) {
                                        $options[$i] = "{$i} / {$max}";
                                    }
                                    return $options;
                                })
                                ->default(0)
                                ->live()
                                ->afterStateUpdated(function ($set, $state, Get $get) {
                                    $max = (int) ($get('veces_al_anio') ?? 1);
                                    $val = (int) $state;
                                })
                                ->required()
                                ->visible(fn (Get $get) => $get('frecuencia') !== 'eventual'),
                            
                            Placeholder::make('ejecuciones_eventuales')
                                ->label('Veces Ejecutado')
                                ->content(fn (Get $get) => $get('ejecuciones_realizadas') ?? 0)
                                ->visible(fn (Get $get) => $get('frecuencia') === 'eventual'),

                            TextInput::make('detalle_frecuencia')
                                ->label('Detalle de Eventualidad')
                                ->placeholder('Especifique la razón (ej. Cuando ocurra, Personal nuevo)')
                                ->required(fn (Get $get) => $get('frecuencia') === 'eventual')
                                ->visible(fn (Get $get) => $get('frecuencia') === 'eventual')
                                ->columnSpanFull(),
                        ]),
                ]),

            Section::make('Fechas y Programación')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('fecha_inicio')
                                ->label(fn (Get $get) => $get('frecuencia') === 'eventual' ? 'Fecha de Ejecución (Si ya ocurrió)' : 'Fecha Inicial')
                                ->placeholder(fn (Get $get) => $get('frecuencia') === 'eventual' ? 'Seleccione la fecha de realización' : 'Seleccione la fecha de inicio')
                                ->helperText(fn (Get $get) => $get('frecuencia') === 'eventual' ? 'Deje en blanco si aún no ha ocurrido.' : null)
                                ->live(), // Actualiza el cronograma
                        ]),

                    Placeholder::make('fechas_programadas_visual')
                        ->label(fn (Get $get) => $get('frecuencia') === 'eventual' ? 'Historial de Ejecuciones' : 'Fechas del año')
                        ->content(function (Get $get, $record) {
                            $fechaInicio = $get('fecha_inicio');
                            $frecuencia = $get('frecuencia');
                            $vecesAlAnio = (int) $get('veces_al_anio');

                            if (!$fechaInicio && !$frecuencia && !$record) {
                                return new HtmlString('<span class="text-gray-500 italic">Seleccione fecha programada y frecuencia para ver el cronograma.</span>');
                            }

                            // Lógica específica para EVENTUALES
                            if ($frecuencia === 'eventual') {
                                $html = '';
                                $detalle = $get('detalle_frecuencia') ? ': ' . htmlspecialchars($get('detalle_frecuencia')) : '';
                                
                                // Mostrar la fecha seleccionada en el formulario (si hay)
                                if ($fechaInicio) {
                                    try {
                                        $date = Carbon::parse($fechaInicio)->format('d/m/Y');
                                        $html .= '<div class="mb-2"><span class="text-gray-500 italic">Nueva ejecución: <strong>' . $date . '</strong></span></div>';
                                    } catch (\Exception $e) {}
                                }

                                // Mostrar historial de ejecuciones pasadas si existe el registro
                                if ($record && $record->executions->count() > 0) {
                                    $html .= '<div class="flex flex-wrap gap-2 mt-2">';
                                    foreach ($record->executions as $execution) {
                                        if ($execution->fecha_ejecucion_real) {
                                            $fecha = Carbon::parse($execution->fecha_ejecucion_real)->format('d/m/Y');
                                            $html .= '<div style="background-color: rgba(var(--success-500), 0.1); color: rgb(var(--success-600)); border: 1px solid rgba(var(--success-500), 0.2);" class="px-3 py-1 rounded-full text-xs font-medium" title="Ejecutado">' . $fecha . '</div>';
                                        }
                                    }
                                    $html .= '</div>';
                                }

                                if (empty($html)) {
                                    return new HtmlString('<span class="text-gray-500 italic">Eventualmente' . $detalle . ' - Sin ejecuciones registradas</span>');
                                }

                                return new HtmlString($html);
                            }

                            // Lógica para PROGRAMADAS (Diario, Semanal, Mensual, etc.)
                            if (!$fechaInicio) {
                                 return new HtmlString('<span class="text-gray-500 italic">Seleccione fecha de inicio.</span>');
                            }
                            
                            try {
                                $date = Carbon::parse($fechaInicio);
                            } catch (\Exception $e) {
                                return 'Fecha inválida';
                            }

                            $fechas = [];
                            // Determinar cuántas fechas mostrar
                            $limit = match($frecuencia) {
                                'diario' => 10,
                                'semanal' => 12,
                                default => $vecesAlAnio > 0 ? $vecesAlAnio : 1,
                            };

                            for ($i = 0; $i < $limit; $i++) {
                                // La primera iteración es la fecha base, las siguientes se suman
                                if ($i > 0) {
                                    switch($frecuencia) {
                                        case 'diario': $date->addDay(); break;
                                        case 'semanal': $date->addWeek(); break;
                                        case 'mensual': $date->addMonth(); break;
                                        case 'trimestral': $date->addMonths(3); break;
                                        case 'semestral': $date->addMonths(6); break;
                                        case 'anual': $date->addYear(); break;
                                    }
                                }
                                $fechas[] = $date->format('d/m/Y');
                            }

                            if ($frecuencia === 'diario' || $frecuencia === 'semanal') {
                                $fechas[] = '...';
                            }

                            $html = '<div class="flex flex-wrap gap-2 mt-2">';
                            foreach ($fechas as $f) {
                                $html .= '<div style="background-color: rgba(var(--primary-500), 0.1); color: rgb(var(--primary-600)); border: 1px solid rgba(var(--primary-500), 0.2);" class="px-3 py-1 rounded-full text-xs font-medium">' . $f . '</div>';
                            }
                            $html .= '</div>';

                            return new HtmlString($html);
                        })
                        ->columnSpanFull(),
                ]),

            Section::make('Metas y Configuración')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('meta')
                                ->label('Meta de Cumplimiento (%)')
                                ->numeric()
                                ->suffix('%')
                                ->default(100),
                            
                            Toggle::make('es_obligatoria')
                                ->label('¿Es obligatoria?')
                                ->default(true)
                                ->inline(false),
                        ]),
                ]),
        ]);
    }
}