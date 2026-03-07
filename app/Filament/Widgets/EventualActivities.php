<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\ActivityExecution;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;
use Illuminate\Database\Eloquent\Builder;

class EventualActivities extends BaseWidget
{
    protected static ?int $sort = 3;

    protected string $view = 'filament.widgets.eventual-activities';

    protected int | string | array $columnSpan = 1;

    protected ?string $pollingInterval = '30s';

    #[On('activity-executed')]
    public function refresh(): void
    {
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->query(
                Activity::query()
                    ->where('frecuencia', 'eventual')
                    ->with(['executions' => function ($query) {
                        $query->whereBetween('created_at', [
                            now()->startOfDay()->utc(),
                            now()->endOfDay()->utc()
                        ]);
                    }])
            )
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Actividad')
                    ->description(fn (Activity $record) => $record->descripcion ?? 'Sin descripción')
                    ->weight('bold')
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('tipo')
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
            ])
            ->actions([
                Action::make('iniciar')
                    ->label(fn (Activity $record) => $this->hasExecutedToday($record) ? 'Completado Hoy' : 'Iniciar')
                    ->icon(fn (Activity $record) => $this->hasExecutedToday($record) ? 'heroicon-o-check-circle' : 'heroicon-o-play')
                    ->color(fn (Activity $record) => $this->hasExecutedToday($record) ? 'gray' : 'success')
                    ->disabled(fn (Activity $record) => $this->hasExecutedToday($record))
                    ->button()
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
                            ->columnSpanFull(),

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
                            ->visible(fn (Activity $record) => $record->tipo === 'auditoria'),

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
                            ->visible(fn (Activity $record) => $record->tipo === 'inspeccion'),

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
                            ->visible(fn (Activity $record) => $record->tipo === 'capacitacion'),

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
                            ->visible(fn (Activity $record) => $record->tipo === 'simulacro'),

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
                            ->visible(fn (Activity $record) => $record->tipo === 'incidente'),

                        \Filament\Schemas\Components\Section::make('Detalles de Comité')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('data.tema_principal')
                                            ->label('Tema Tratado'),
                                    ]),
                            ])
                            ->visible(fn (Activity $record) => $record->tipo === 'comite'),

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
                            ->visible(fn (Activity $record) => $record->tipo === 'documentacion'),

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
                            ->visible(fn (Activity $record) => $record->tipo === 'promocion'),

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
                            ->visible(fn (Activity $record) => $record->tipo === 'control_operacional'),
                    ])
                    ->modalHeading('Ejecutar Actividad Eventual')
                    ->modalSubmitActionLabel('Guardar')
                    ->action(function (Activity $record, array $data) {
                        \Illuminate\Support\Facades\Log::info('--- INICIO GUARDADO ACTIVIDAD EVENTUAL ---');
                        
                        $evidenciaLocalPath = $data['evidencia'] ?? null;
                        $finalDrivePath = null;
                        \Illuminate\Support\Facades\Log::info("Ruta evidencia local recibida: " . ($evidenciaLocalPath ?? 'NULL'));
                        
                        if ($evidenciaLocalPath) {
                            $targetDirectory = \App\Services\DrivePathGenerator::generate($record);
                            $fileName = basename($evidenciaLocalPath);
                            
                            // Asegurarnos de que no haya doble barra
                            $targetPath = trim($targetDirectory, '/') . '/' . $fileName;
                            
                            \Illuminate\Support\Facades\Log::info("Target Directory: {$targetDirectory}");
                            \Illuminate\Support\Facades\Log::info("Target Path calculado: {$targetPath}");
                            
                            try {
                                $localDisk = \Illuminate\Support\Facades\Storage::disk('public');
                                $googleDisk = \Illuminate\Support\Facades\Storage::disk('google');
                                
                                if ($localDisk->exists($evidenciaLocalPath)) {
                                    \Illuminate\Support\Facades\Log::info("EventualActivities: Subiendo archivo local a Google Drive...");
                                    
                                    if (!$googleDisk->exists($targetDirectory)) {
                                         $googleDisk->makeDirectory($targetDirectory);
                                    }

                                    $fileContents = $localDisk->get($evidenciaLocalPath);
                                    $googleDisk->put($targetPath, $fileContents);
                                    
                                    if ($googleDisk->exists($targetPath)) {
                                        $finalDrivePath = $targetPath;
                                        $localDisk->delete($evidenciaLocalPath);
                                        \Illuminate\Support\Facades\Log::info("EventualActivities: Archivo subido y local eliminado.");
                                    }
                                } else {
                                    \Illuminate\Support\Facades\Log::warning("EventualActivities: Archivo local no encontrado: {$evidenciaLocalPath}");
                                }
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("EventualActivities: Error subida: " . $e->getMessage());
                            }
                        }

                        ActivityExecution::create([
                            'activity_id' => $record->id,
                            'observacion' => $data['observacion'] ?? null,
                            'evidencia' => $finalDrivePath,
                            'estado' => \App\Enums\ActivityState::EJECUTADO,
                            'fecha_ejecucion_real' => now(),
                            'fecha_programada' => null,
                            'data' => $data['data'] ?? [],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Actividad Eventual Ejecutada')
                            ->success()
                            ->send();

                        $this->dispatch('activity-executed');
                        \Illuminate\Support\Facades\Log::info('--- FIN GUARDADO ACTIVIDAD EVENTUAL ---');
                    }),
            ])
            ->paginated(false);
    }

    protected function hasExecutedToday(Activity $activity): bool
    {
        return $activity->executions->isNotEmpty();
    }
}
