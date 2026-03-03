<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use App\Models\ActivityExecution;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\FileUpload;
use App\Models\Supervisor;
use App\Models\Location;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Placeholder;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Actividad')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'primary' => 'general',
                        'warning' => 'auditoria',
                        'success' => 'inspeccion',
                        'info' => 'capacitacion',
                        'danger' => ['simulacro', 'incidente'],
                        'gray' => 'documentacion',
                        'success' => ['promocion', 'inspeccion'],
                        'warning' => ['control_operacional', 'auditoria'],
                        'info' => ['comite', 'capacitacion'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'auditoria' => 'Auditoría',
                        'inspeccion' => 'Inspección',
                        'capacitacion' => 'Capacitación',
                        'simulacro' => 'Simulacro',
                        'incidente' => 'Incidente',
                        'comite' => 'Comité',
                        'documentacion' => 'Documentación',
                        'promocion' => 'Promoción',
                        'control_operacional' => 'Control Operacional',
                        default => ucfirst($state),
                    }),

                TextColumn::make('component.program.nombre')
                    ->label('Programa')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('component.full_name')
                    ->label('Componente / Elemento')
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('component', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->wrap(),

                TextColumn::make('location.nombre')
                    ->label('Sede')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('frecuencia')
                    ->label('Frecuencia')
                    ->sortable()
                    ->badge(),

                TextColumn::make('veces_al_anio')
                    ->label('Veces/Año')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('responsable.nombre')
                    ->label('Cargo Responsable')
                    ->searchable(),

                TextColumn::make('responsableDelegado.nombre')
                    ->label('Cargo Responsable Delegado')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('apoyo')
                    ->label('Apoyo')
                    ->searchable()
                    ->toggleable(),
            ])
            ->actions([
                Action::make('executions')
                    ->label('Gestionar Ejecuciones')
                    ->icon('heroicon-m-calendar-days')
                    ->color('info')
                    ->form(function (\App\Models\Activity $record) {
                        $executions = $record->executions()->orderBy('fecha_programada')->get();

                        // Campos comunes para reutilizar
                        $getFields = function ($execution, $prefix = '') use ($record) {
                            $fieldName = fn($name) => $prefix ? "{$prefix}.{$name}" : $name;
                            $getData = fn($key) => $execution->data[$key] ?? null;
                            
                            $fields = [
                                Hidden::make($fieldName('id'))
                                    ->default($execution->id),
                                    
                                \Filament\Forms\Components\DatePicker::make($fieldName('fecha_programada'))
                                    ->label('Fecha Programada')
                                    ->default($execution->fecha_programada)
                                    ->disabled()
                                    ->required(),
                                    
                                \Filament\Forms\Components\Select::make($fieldName('estado'))
                                    ->label('Estado')
                                    ->options(\App\Enums\ActivityState::class)
                                    ->default($execution->estado)
                                    ->required(),
                                    
                                \Filament\Forms\Components\DatePicker::make($fieldName('fecha_ejecucion_real'))
                                    ->label('Fecha Real')
                                    ->default($execution->fecha_ejecucion_real),
                                    
                                Textarea::make($fieldName('observacion'))
                                        ->label('Observaciones')
                                        ->default($execution->observacion)
                                        ->rows(2)
                                        ->columnSpanFull(),
                                        
                                    Placeholder::make($fieldName('evidencias_grid'))
                                        ->label('Evidencias Existentes')
                                        ->content(function () use ($execution) {
                                            $files = [];
                                            $evidences = $execution->evidencia;
                                            
                                            if (is_string($evidences)) {
                                                $decoded = json_decode($evidences, true);
                                                $evidences = is_array($decoded) ? $decoded : [$evidences];
                                            }

                                            if (is_array($evidences)) {
                                                foreach ($evidences as $path) {
                                                    try {
                                                        $disk = Storage::disk('google');
                                                        $files[] = [
                                                            'path' => $path,
                                                            'name' => basename($path),
                                                            'mime' => $disk->mimeType($path) ?? 'application/octet-stream',
                                                            'size' => $disk->size($path) ?? 0,
                                                        ];
                                                    } catch (\Exception $e) {
                                                         $files[] = [
                                                            'path' => $path,
                                                            'name' => basename($path),
                                                            'mime' => 'application/octet-stream',
                                                            'size' => 0,
                                                        ];
                                                    }
                                                }
                                            }
                                            
                                            return view('filament.components.evidence-grid', [
                                                'files' => $files, 
                                                'mode' => 'edit',
                                                'recordId' => $execution->id
                                            ]);
                                        })
                                        ->columnSpanFull(),

                                    \Filament\Forms\Components\FileUpload::make($fieldName('new_evidencia'))
                                        ->label('Agregar Nuevas Evidencias')
                                        ->disk('public')
                                        ->directory('temp-uploads')
                                        ->multiple()
                                        ->preserveFilenames()
                                        ->columnSpanFull(),
                                ];

                            // Campos dinámicos según el tipo de actividad
                            if ($record->tipo === 'auditoria') {
                                $fields[] = Section::make('Detalles de Auditoría')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make($fieldName('data.auditor_id'))
                                                    ->label('Supervisor Auditor')
                                                    ->options(Supervisor::pluck('nombre', 'id'))
                                                    ->default($getData('auditor_id'))
                                                    ->searchable()
                                                    ->preload(),
                                                Textarea::make($fieldName('data.hallazgos'))
                                                    ->label('Hallazgos')
                                                    ->default($getData('hallazgos')),
                                            ]),
                                    ]);
                            } elseif ($record->tipo === 'inspeccion') {
                                $fields[] = Section::make('Detalles de Inspección')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make($fieldName('data.location_id'))
                                                    ->label('Ubicación')
                                                    ->options(Location::pluck('nombre', 'id'))
                                                    ->default($getData('location_id'))
                                                    ->searchable()
                                                    ->preload(),
                                                Textarea::make($fieldName('data.observaciones'))
                                                    ->label('Observaciones Adicionales')
                                                    ->default($getData('observaciones')),
                                            ]),
                                    ]);
                            } elseif ($record->tipo === 'capacitacion') {
                                $fields[] = Section::make('Detalles de Capacitación')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make($fieldName('data.tema'))
                                                    ->label('Tema Específico')
                                                    ->default($getData('tema')),
                                                TimePicker::make($fieldName('data.hora_inicio'))
                                                    ->label('Hora de Inicio')
                                                    ->default($getData('hora_inicio')),
                                                TextInput::make($fieldName('data.duracion_horas'))
                                                    ->label('Duración Real (Horas)')
                                                    ->numeric()
                                                    ->default($getData('duracion_horas')),
                                                TextInput::make($fieldName('data.asistentes_reales'))
                                                    ->label('Asistentes Reales')
                                                    ->numeric()
                                                    ->default($getData('asistentes_reales')),
                                            ]),
                                    ]);
                            } elseif ($record->tipo === 'simulacro') {
                                $fields[] = Section::make('Detalles de Simulacro')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make($fieldName('data.escenario'))
                                                    ->label('Escenario del Simulacro')
                                                    ->default($getData('escenario')),
                                                TextInput::make($fieldName('data.participantes_count'))
                                                    ->label('Participantes Reales')
                                                    ->numeric()
                                                    ->default($getData('participantes_count')),
                                            ]),
                                    ]);
                            } elseif ($record->tipo === 'incidente') {
                                $fields[] = Section::make('Detalles de Incidente')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make($fieldName('data.lugar'))
                                                    ->label('Lugar del Incidente')
                                                    ->default($getData('lugar')),
                                                Select::make($fieldName('data.severidad'))
                                                    ->label('Severidad')
                                                    ->options([
                                                        'leve' => 'Leve',
                                                        'moderado' => 'Moderado',
                                                        'grave' => 'Grave',
                                                        'critico' => 'Crítico',
                                                    ])
                                                    ->default($getData('severidad')),
                                            ]),
                                    ]);
                            } elseif ($record->tipo === 'comite') {
                                $fields[] = Section::make('Detalles de Comité')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make($fieldName('data.tema_principal'))
                                                    ->label('Tema Tratado')
                                                    ->default($getData('tema_principal')),
                                            ]),
                                    ]);
                            } elseif ($record->tipo === 'documentacion') {
                                $fields[] = Section::make('Detalles de Documentación')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make($fieldName('data.tipo_documento'))
                                                    ->label('Tipo de Documento')
                                                    ->options([
                                                        'procedimiento' => 'Procedimiento',
                                                        'formato' => 'Formato',
                                                        'politica' => 'Política',
                                                        'manual' => 'Manual',
                                                        'otro' => 'Otro',
                                                    ])
                                                    ->default($getData('tipo_documento')),
                                                TextInput::make($fieldName('data.version'))
                                                    ->label('Versión Generada')
                                                    ->default($getData('version')),
                                            ]),
                                    ]);
                            } elseif ($record->tipo === 'promocion') {
                                $fields[] = Section::make('Detalles de Promoción')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make($fieldName('data.publico_objetivo'))
                                                    ->label('Público Alcanzado')
                                                    ->default($getData('publico_objetivo')),
                                                TextInput::make($fieldName('data.material_entregado'))
                                                    ->label('Material Entregado')
                                                    ->default($getData('material_entregado')),
                                                TextInput::make($fieldName('data.participantes_estimados'))
                                                    ->label('Participantes Totales')
                                                    ->numeric()
                                                    ->default($getData('participantes_estimados')),
                                            ]),
                                    ]);
                            } elseif ($record->tipo === 'control_operacional') {
                                $fields[] = Section::make('Detalles de Control Operacional')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make($fieldName('data.parametro'))
                                                    ->label('Parámetro Controlado')
                                                    ->default($getData('parametro')),
                                                TextInput::make($fieldName('data.valor_obtenido'))
                                                    ->label('Valor Obtenido')
                                                    ->default($getData('valor_obtenido')),
                                            ]),
                                    ]);
                            }

                            return $fields;
                        };

                        // Caso 1: Una sola ejecución (o ninguna)
                        if ($executions->count() <= 1) {
                            $execution = $executions->first();
                            if (!$execution) {
                                return [
                                    \Filament\Forms\Components\Placeholder::make('no_data')
                                        ->content('No hay ejecuciones programadas para esta actividad.')
                                ];
                            }
                            
                            return $getFields($execution, 'single_execution');
                        }

                        // Caso 2: Múltiples ejecuciones (Tabs/Burbujas)
                        $tabs = [];
                        foreach ($executions as $execution) {
                            // Formatear nombre del mes (ej: Ene, Feb)
                            $monthName = \Carbon\Carbon::parse($execution->fecha_programada)->locale('es')->translatedFormat('M');
                            $day = \Carbon\Carbon::parse($execution->fecha_programada)->format('d');
                            
                            $tabs[] = \Filament\Schemas\Components\Tabs\Tab::make("{$monthName} {$day}")
                                ->schema($getFields($execution, "executions.{$execution->id}"));
                        }

                        return [
                            \Filament\Schemas\Components\Tabs::make('Ejecuciones')
                                ->tabs($tabs)
                                ->persistTabInQueryString('execution_tab')
                        ];
                    })
                    ->action(function (array $data) {
                        $saveExecution = function ($id, $item) {
                            $extraData = $item['data'] ?? [];

                            $execution = ActivityExecution::find($id);
                            if (!$execution) return;

                            $oldEvidences = $execution->evidencia;
                            if (is_string($oldEvidences)) {
                                $decoded = json_decode($oldEvidences, true);
                                $oldEvidences = is_array($decoded) ? $decoded : [$oldEvidences];
                            }
                            $oldEvidences = $oldEvidences ?? [];
                            
                            // Procesar nuevos archivos
                            $newEvidences = $item['new_evidencia'] ?? [];
                            $finalNewPaths = [];

                            if (!empty($newEvidences)) {
                                $targetDir = \App\Services\DrivePathGenerator::generate($execution);
                                if (!Storage::disk('google')->exists($targetDir)) {
                                     Storage::disk('google')->makeDirectory($targetDir);
                                }

                                foreach ($newEvidences as $tempPath) {
                                    if (Storage::disk('public')->exists($tempPath)) {
                                        $fileName = basename($tempPath);
                                        $targetPath = trim($targetDir, '/') . '/' . $fileName;
                                        
                                        Storage::disk('google')->put($targetPath, Storage::disk('public')->get($tempPath));
                                        if (Storage::disk('google')->exists($targetPath)) {
                                            $finalNewPaths[] = $targetPath;
                                            Storage::disk('public')->delete($tempPath);
                                        }
                                    }
                                }
                            }
                            
                            $finalEvidences = array_merge($oldEvidences, $finalNewPaths);

                            $execution->update([
                                'estado' => $item['estado'],
                                'fecha_ejecucion_real' => $item['fecha_ejecucion_real'],
                                'observacion' => $item['observacion'],
                                'evidencia' => $finalEvidences,
                                'data' => $extraData,
                            ]);
                        };

                        // Guardar Caso 1
                        if (isset($data['single_execution'])) {
                            $item = $data['single_execution'];
                            $saveExecution($item['id'], $item);
                        }
                        // Guardar Caso 2
                        elseif (isset($data['executions'])) {
                            foreach ($data['executions'] as $id => $item) {
                                $saveExecution($id, $item);
                            }
                        }
                        
                        Notification::make()
                            ->title('Ejecuciones actualizadas correctamente')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('Guardar Cambios')
                    ->modalWidth('4xl'),
                
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
