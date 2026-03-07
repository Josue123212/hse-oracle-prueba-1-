<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

class ActivitiesForToday extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '30s';

    #[On('activity-updated')]
    public function refresh(): void
    {
    }

    // Limit height to approx 6 items + header and enable scrolling

    protected string $view = 'filament.widgets.activities-for-today';

    public function table(Table $table): Table
    {
        return $table
            ->extraAttributes([
                // 'class' => 'overflow-y-auto overflow-x-auto', 
                // 'style' => 'max-height: 350px !important;',
            ])
            ->query(
                \App\Models\ActivityExecution::query()
                    ->with('activity')
                    // El GlobalScope (ProgramScope) se encarga de filtrar por programa si está en sesión
                    ->whereDate('fecha_programada', now())
                    ->whereIn('estado', [
                        \App\Enums\ActivityState::PROGRAMADO,
                        \App\Enums\ActivityState::EN_PROCESO,
                        \App\Enums\ActivityState::EJECUTADO
                    ])
            )
            ->heading(null)
            ->columns([
                Tables\Columns\TextColumn::make('activity.nombre')
                    ->label('Actividad')
                    ->description(fn (\App\Models\ActivityExecution $record) => $record->activity->descripcion ?? 'Sin descripción')
                    ->weight('bold')
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('activity.tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'inspeccion' => 'info',
                        'capacitacion' => 'success',
                        'simulacro' => 'warning',
                        'auditoria' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado Hoy')
                    ->badge(),
            ])
            ->actions([
                Action::make('iniciar')
                    ->label(fn (\App\Models\ActivityExecution $record) => match ($record->estado) {
                        \App\Enums\ActivityState::EJECUTADO => 'Completado',
                        \App\Enums\ActivityState::EN_PROCESO => 'Continuar',
                        default => 'Iniciar',
                    })
                    ->icon(fn (\App\Models\ActivityExecution $record) => match ($record->estado) {
                        \App\Enums\ActivityState::EJECUTADO => 'heroicon-o-check-circle',
                        default => 'heroicon-o-play',
                    })
                    ->color(fn (\App\Models\ActivityExecution $record) => match ($record->estado) {
                        \App\Enums\ActivityState::EJECUTADO => 'gray',
                        default => 'success',
                    })
                    ->disabled(fn (\App\Models\ActivityExecution $record) => $record->estado === \App\Enums\ActivityState::EJECUTADO)
                    ->button()
                    ->mountUsing(function (\App\Models\ActivityExecution $record) {
                        if ($record->estado === \App\Enums\ActivityState::PROGRAMADO) {
                            $record->update(['estado' => \App\Enums\ActivityState::EN_PROCESO]);
                        }
                    })
                    ->form([
                        \Filament\Forms\Components\Textarea::make('observacion')
                    ->label('Observaciones')
                    ->rows(3)
                    ->columnSpanFull(),
                \Filament\Forms\Components\FileUpload::make('evidencia')
                    ->label('Evidencia (Archivo)')
                    ->disk('public') // Cambiamos a disco local temporalmente
                    ->directory('temp-uploads') // Directorio temporal local
                    ->visibility('private')
                    ->preserveFilenames()
                    // ->multiple() // Desactivado temporalmente para simplificar depuración si no es necesario
                    // ->storeFileNamesIn('data->file_names')
                    ->columnSpanFull()
                    ->required(), // Aseguramos que se requiera evidencia para completar la actividad

                \Filament\Schemas\Components\Section::make('Detalles de Auditoría')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\Select::make('data.auditor_id')
                                    ->label('Supervisor')
                                    ->options(\App\Models\Supervisor::pluck('nombre', 'id'))
                                    ->searchable()
                                    ->preload(),
                                \Filament\Forms\Components\Textarea::make('data.hallazgos')
                                    ->label('Hallazgos'),
                            ]),
                    ])
                    ->visible(fn (\App\Models\ActivityExecution $record) => $record->activity?->tipo === 'auditoria'),

                \Filament\Schemas\Components\Section::make('Detalles de Inspección')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\Select::make('data.location_id')
                                    ->label('Ubicación')
                                    ->options(\App\Models\Location::pluck('nombre', 'id'))
                                    ->searchable()
                                    ->preload(),
                                \Filament\Forms\Components\Textarea::make('data.observaciones')
                                    ->label('Observaciones Adicionales'),
                            ]),
                    ])
                    ->visible(fn (\App\Models\ActivityExecution $record) => $record->activity?->tipo === 'inspeccion'),

                \Filament\Schemas\Components\Section::make('Detalles de Capacitación')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('data.tema')
                                    ->label('Tema Específico'),
                                \Filament\Forms\Components\TimePicker::make('data.hora_inicio')
                                    ->label('Hora de Inicio'),
                                \Filament\Forms\Components\TextInput::make('data.duracion_horas')
                                    ->label('Duración Real (Horas)')
                                    ->numeric(),
                                \Filament\Forms\Components\TextInput::make('data.asistentes_reales')
                                    ->label('Asistentes Reales')
                                    ->numeric(),
                            ]),
                    ])
                    ->visible(fn (\App\Models\ActivityExecution $record) => $record->activity?->tipo === 'capacitacion'),

                \Filament\Schemas\Components\Section::make('Detalles de Simulacro')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('data.escenario')
                                    ->label('Escenario del Simulacro'),
                                \Filament\Forms\Components\TextInput::make('data.participantes_count')
                                    ->label('Participantes Reales')
                                    ->numeric(),
                            ]),
                    ])
                    ->visible(fn (\App\Models\ActivityExecution $record) => $record->activity?->tipo === 'simulacro'),

                \Filament\Schemas\Components\Section::make('Detalles de Incidente')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('data.lugar')
                                    ->label('Lugar del Incidente'),
                                \Filament\Forms\Components\Select::make('data.severidad')
                                    ->label('Severidad')
                                    ->options([
                                        'leve' => 'Leve',
                                        'moderado' => 'Moderado',
                                        'grave' => 'Grave',
                                        'critico' => 'Crítico',
                                    ]),
                            ]),
                    ])
                    ->visible(fn (\App\Models\ActivityExecution $record) => $record->activity?->tipo === 'incidente'),

                \Filament\Schemas\Components\Section::make('Detalles de Comité')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('data.tema_principal')
                                    ->label('Tema Tratado'),
                            ]),
                    ])
                    ->visible(fn (\App\Models\ActivityExecution $record) => $record->activity?->tipo === 'comite'),

                \Filament\Schemas\Components\Section::make('Detalles de Documentación')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\Select::make('data.tipo_documento')
                                    ->label('Tipo de Documento')
                                    ->options([
                                        'procedimiento' => 'Procedimiento',
                                        'formato' => 'Formato',
                                        'politica' => 'Política',
                                        'manual' => 'Manual',
                                        'otro' => 'Otro',
                                    ]),
                                \Filament\Forms\Components\TextInput::make('data.version')
                                    ->label('Versión Generada'),
                            ]),
                    ])
                    ->visible(fn (\App\Models\ActivityExecution $record) => $record->activity?->tipo === 'documentacion'),

                \Filament\Schemas\Components\Section::make('Detalles de Promoción')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('data.publico_objetivo')
                                    ->label('Público Alcanzado'),
                                \Filament\Forms\Components\TextInput::make('data.material_entregado')
                                    ->label('Material Entregado'),
                                \Filament\Forms\Components\TextInput::make('data.participantes_estimados')
                                    ->label('Participantes Totales')
                                    ->numeric(),
                            ]),
                    ])
                    ->visible(fn (\App\Models\ActivityExecution $record) => $record->activity?->tipo === 'promocion'),

                \Filament\Schemas\Components\Section::make('Detalles de Control Operacional')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('data.parametro')
                                    ->label('Parámetro Controlado'),
                                \Filament\Forms\Components\TextInput::make('data.valor_obtenido')
                                    ->label('Valor Obtenido'),
                            ]),
                    ])
                    ->visible(fn (\App\Models\ActivityExecution $record) => $record->activity?->tipo === 'control_operacional'),
                    ])
                    ->modalHeading('Ejecutar Actividad')
                    ->modalSubmitActionLabel('Guardar')
                    ->action(function (\App\Models\ActivityExecution $record, array $data) {
                        \Illuminate\Support\Facades\Log::info('--- INICIO GUARDADO ACTIVIDAD PROGRAMADA ---');
                        
                        $evidenciaLocalPath = $data['evidencia'] ?? null;
                        // Si es array (multiple uploads), tomamos el primero
                        if (is_array($evidenciaLocalPath)) {
                            $evidenciaLocalPath = reset($evidenciaLocalPath);
                        }

                        \Illuminate\Support\Facades\Log::info("Ruta local evidencia: " . ($evidenciaLocalPath ?? 'NULL'));

                        $finalDrivePath = null;
                        $evidenciaToSave = null;

                        if ($evidenciaLocalPath) {
                            $targetDirectory = \App\Services\DrivePathGenerator::generate($record->activity);
                            $fileName = basename($evidenciaLocalPath);
                            
                            // Asegurar que la ruta no tenga dobles slashes
                            $targetPath = trim($targetDirectory, '/') . '/' . $fileName;
                            
                            try {
                                $localDisk = \Illuminate\Support\Facades\Storage::disk('public');
                                $googleDisk = \Illuminate\Support\Facades\Storage::disk('google');
                                
                                if ($localDisk->exists($evidenciaLocalPath)) {
                                    \Illuminate\Support\Facades\Log::info("Subiendo archivo local a Google Drive (Ruta: $targetPath)...");
                                    
                                    // Asegurar directorio destino en Drive
                                    if (!$googleDisk->exists($targetDirectory)) {
                                        $googleDisk->makeDirectory($targetDirectory);
                                    }
                                    
                                    // Leer archivo local y subir a Drive
                                    $fileContents = $localDisk->get($evidenciaLocalPath);
                                    $putResult = $googleDisk->put($targetPath, $fileContents);
                                    
                                    if ($putResult) {
                                        $finalDrivePath = $targetPath;
                                        \Illuminate\Support\Facades\Log::info("¡Archivo subido exitosamente a Drive!: {$finalDrivePath}");
                                        
                                        // Opcional: Eliminar el temporal local
                                        $localDisk->delete($evidenciaLocalPath);
                                    } else {
                                        \Illuminate\Support\Facades\Log::error("Fallo al subir el archivo a Drive (put devolvió false).");
                                        throw new \Exception("Fallo al subir el archivo a Drive.");
                                    }
                                } else {
                                     \Illuminate\Support\Facades\Log::warning("El archivo local no se encuentra: {$evidenciaLocalPath}");
                                }
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("ActivitiesForToday: Error subiendo archivo a Drive: " . $e->getMessage());
                                \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Error al subir evidencia')
                                    ->body('No se pudo subir el archivo a Google Drive. Por favor intente nuevamente.')
                                    ->danger()
                                    ->send();
                                    
                                // Detener ejecución para no guardar estado "Ejecutado" sin evidencia
                                return;
                            }
                        } else {
                             \Illuminate\Support\Facades\Log::warning("No se proporcionó evidencia (evidenciaLocalPath vacío).");
                        }

                        // Guardar la evidencia como JSON array string para compatibilidad con el sistema
                        // Si ya existen evidencias, podríamos querer fusionarlas, pero en "ActivitiesForToday"
                        // asumimos que es la primera ejecución. Por si acaso, fusionamos.
                        $currentEvidences = $record->evidencia;
                        if (is_string($currentEvidences)) {
                            $decoded = json_decode($currentEvidences, true);
                            $currentEvidences = is_array($decoded) ? $decoded : [$currentEvidences];
                        }
                        $currentEvidences = is_array($currentEvidences) ? $currentEvidences : [];
                        
                        if ($finalDrivePath) {
                            $currentEvidences[] = $finalDrivePath;
                        }
                        
                        $evidenciaToSave = !empty($currentEvidences) ? json_encode($currentEvidences) : null;

                        $record->update([
                            'observacion' => $data['observacion'] ?? null,
                            'evidencia' => $evidenciaToSave, // Guardamos como JSON array
                            'estado' => \App\Enums\ActivityState::EJECUTADO,
                            'fecha_ejecucion_real' => now(), // Se llena con la fecha actual de ejecución
                            'data' => $data['data'] ?? [],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Actividad Ejecutada')
                            ->success()
                            ->send();
                            
                        \Illuminate\Support\Facades\Log::info('--- FIN GUARDADO ACTIVIDAD PROGRAMADA ---');
                    }),
            ])
            ->emptyStateHeading('No hay actividades pendientes para hoy')
            ->emptyStateDescription('¡Excelente trabajo! Has completado todas las tareas programadas.')
            ->paginated(false);
    }
}
