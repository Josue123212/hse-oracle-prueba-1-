<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\IconEntry;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle de Actividad')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('nombre')
                                    ->label('Actividad')
                                    ->columnSpan(2),
                                
                                TextEntry::make('program.nombre')
                                    ->label('Programa'),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('responsable.name')
                                    ->label('Responsable'),

                                TextEntry::make('frecuencia')
                                    ->label('Frecuencia'),
                                
                                TextEntry::make('fecha_inicio')
                                    ->label('Fecha Programada')
                                    ->date(),

                                TextEntry::make('veces_al_anio')
                                    ->label('Veces al Año'),

                                TextEntry::make('meta')
                                    ->label('Meta')
                                    ->suffix('%'),
                            ]),
                        
                        IconEntry::make('es_obligatoria')
                            ->label('Obligatoria')
                            ->boolean(),
                    ]),

                Section::make('Detalles de Auditoría')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('audit.auditor.name')
                                    ->label('Auditor'),
                                TextEntry::make('audit.hallazgos')
                                    ->label('Hallazgos'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->tipo === 'auditoria'),

                Section::make('Detalles de Inspección')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('inspection.location.nombre')
                                    ->label('Ubicación'),
                                TextEntry::make('inspection.observaciones')
                                    ->label('Observaciones'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->tipo === 'inspeccion'),

                Section::make('Detalles de Capacitación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('training.tema')
                                    ->label('Tema Específico'),
                                TextEntry::make('training.hora_inicio')
                                    ->label('Hora de Inicio'),
                                TextEntry::make('training.duracion_horas')
                                    ->label('Duración (Horas)'),
                                TextEntry::make('training.asistentes_esperados')
                                    ->label('Asistentes Esperados'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->tipo === 'capacitacion'),

                Section::make('Detalles de Simulacro')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('drill.escenario')
                                    ->label('Escenario del Simulacro'),
                                TextEntry::make('drill.participantes_count')
                                    ->label('Participantes Estimados'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->tipo === 'simulacro'),

                Section::make('Detalles de Incidente')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('incident.lugar')
                                    ->label('Lugar del Incidente'),
                                TextEntry::make('incident.severidad')
                                    ->label('Severidad'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->tipo === 'incidente'),

                Section::make('Detalles de Comité')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('committee.tema_principal')
                                    ->label('Tema Principal'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->tipo === 'comite'),

                Section::make('Detalles de Documentación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('documentation.tipo_documento')
                                    ->label('Tipo de Documento'),
                                TextEntry::make('documentation.version')
                                    ->label('Versión'),
                                TextEntry::make('documentation.archivo_path')
                                    ->label('Archivo del Documento'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->tipo === 'documentacion'),

                Section::make('Detalles de Promoción')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('promotion.publico_objetivo')
                                    ->label('Público Objetivo'),
                                TextEntry::make('promotion.material_entregado')
                                    ->label('Material Entregado'),
                                TextEntry::make('promotion.participantes_estimados')
                                    ->label('Participantes Estimados'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->tipo === 'promocion'),

                Section::make('Detalles de Control Operacional')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('operationalControl.parametro')
                                    ->label('Parámetro a Controlar'),
                                TextEntry::make('operationalControl.valor_esperado')
                                    ->label('Valor Esperado'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->tipo === 'control_operacional'),
            ]);
    }
}
