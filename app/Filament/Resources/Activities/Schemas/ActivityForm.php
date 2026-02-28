<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Toggle;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
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
                                ->label('Responsable')
                                ->relationship('responsable', 'name')
                                ->searchable()
                                ->preload(),
                            
                            Select::make('estado')
                                ->label('Estado')
                                ->options(\App\Enums\ActivityState::class)
                                ->default(\App\Enums\ActivityState::PROGRAMADO->value)
                                ->required(),

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
                                    $set('estado', 'programado');
                                })
                                ->required(),

                            TextInput::make('veces_al_anio')
                                ->label('Veces al Año')
                                ->numeric()
                                ->default(1)
                                ->readOnly()
                                ->required(),

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
                                    
                                    if ($val == 0) {
                                        $set('estado', 'programado');
                                    } elseif ($val >= $max) {
                                        $set('estado', 'ejecutado');
                                    } else {
                                        $set('estado', 'en_proceso');
                                    }
                                })
                                ->required(),

                            TextInput::make('detalle_frecuencia')
                                ->label('Detalle de Eventualidad')
                                ->placeholder('Especifique la razón (ej. Cuando ocurra, Personal nuevo)')
                                ->required(fn (Get $get) => $get('frecuencia') === 'eventual')
                                ->visible(fn (Get $get) => $get('frecuencia') === 'eventual')
                                ->columnSpanFull(),
                            
                            Select::make('estado')
                                ->label('Estado')
                                ->options([
                                    'programado' => 'Programado',
                                    'en_proceso' => 'En Proceso',
                                    'ejecutado' => 'Ejecutado',
                                    'no_cumplio' => 'No Cumplió',
                                ])
                                ->default('programado')
                                ->required(),
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
                        ->label('Fechas del año')
                        ->content(function (Get $get) {
                            $fechaInicio = $get('fecha_inicio');
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

            Section::make('Detalles de Auditoría')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('audit_auditor_id')
                                ->label('Auditor')
                                ->options(\App\Models\User::pluck('name', 'id'))
                                ->searchable()
                                ->preload(),
                            Textarea::make('audit_hallazgos')
                                ->label('Hallazgos'),
                        ]),
                ])
                ->hidden(fn (Get $get) => $get('tipo') !== 'auditoria'),

            Section::make('Detalles de Inspección')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('inspection_location_id')
                                ->label('Ubicación')
                                ->options(\App\Models\Location::pluck('nombre', 'id'))
                                ->searchable()
                                ->preload(),
                            Textarea::make('inspection_observaciones')
                                ->label('Observaciones'),
                        ]),
                ])
                ->hidden(fn (Get $get) => $get('tipo') !== 'inspeccion'),

            Section::make('Detalles de Capacitación')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('training_tema')
                                ->label('Tema Específico'),
                            TimePicker::make('training_hora_inicio')
                                ->label('Hora de Inicio'),
                            TextInput::make('training_duracion_horas')
                                ->label('Duración (Horas)')
                                ->numeric()
                                ->default(1),
                            TextInput::make('training_asistentes_esperados')
                                ->label('Asistentes Esperados')
                                ->numeric()
                                ->default(0),
                        ]),
                ])
                ->hidden(fn (Get $get) => $get('tipo') !== 'capacitacion'),

            Section::make('Detalles de Simulacro')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('drill_escenario')
                                ->label('Escenario del Simulacro')
                                ->placeholder('Ej. Sismo, Incendio'),
                            TextInput::make('drill_participantes_count')
                                ->label('Participantes Estimados')
                                ->numeric()
                                ->default(0),
                        ]),
                ])
                ->hidden(fn (Get $get) => $get('tipo') !== 'simulacro'),

            Section::make('Detalles de Incidente')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('incident_lugar')
                                ->label('Lugar del Incidente'),
                            Select::make('incident_severidad')
                                ->label('Severidad')
                                ->options([
                                    'leve' => 'Leve',
                                    'moderado' => 'Moderado',
                                    'grave' => 'Grave',
                                    'critico' => 'Crítico',
                                ])
                                ->default('leve'),
                        ]),
                ])
                ->hidden(fn (Get $get) => $get('tipo') !== 'incidente'),

            Section::make('Detalles de Comité')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('committee_tema_principal')
                                ->label('Tema Principal'),
                        ]),
                ])
                ->hidden(fn (Get $get) => $get('tipo') !== 'comite'),

            Section::make('Detalles de Documentación')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('documentation_tipo_documento')
                                ->label('Tipo de Documento')
                                ->options([
                                    'procedimiento' => 'Procedimiento',
                                    'formato' => 'Formato',
                                    'politica' => 'Política',
                                    'manual' => 'Manual',
                                    'otro' => 'Otro',
                                ])
                                ->required(),
                            TextInput::make('documentation_version')
                                ->label('Versión')
                                ->default('1.0'),
                            FileUpload::make('documentation_archivo_path')
                                ->label('Archivo del Documento')
                                ->directory('documentacion'),
                        ]),
                ])
                ->hidden(fn (Get $get) => $get('tipo') !== 'documentacion'),

            Section::make('Detalles de Promoción')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('promotion_publico_objetivo')
                                ->label('Público Objetivo'),
                            TextInput::make('promotion_material_entregado')
                                ->label('Material Entregado'),
                            TextInput::make('promotion_participantes_estimados')
                                ->label('Participantes Estimados')
                                ->numeric()
                                ->default(0),
                        ]),
                ])
                ->hidden(fn (Get $get) => $get('tipo') !== 'promocion'),

            Section::make('Detalles de Control Operacional')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('operational_control_parametro')
                                ->label('Parámetro a Controlar')
                                ->required(),
                            TextInput::make('operational_control_valor_esperado')
                                ->label('Valor Esperado')
                                ->required(),
                        ]),
                ])
                ->hidden(fn (Get $get) => $get('tipo') !== 'control_operacional'),
        ]);
    }
}