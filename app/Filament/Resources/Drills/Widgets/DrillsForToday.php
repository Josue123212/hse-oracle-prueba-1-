<?php

namespace App\Filament\Resources\Drills\Widgets;

use App\Models\ActivityExecution;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

class DrillsForToday extends BaseWidget
{
    use \App\Filament\Traits\HasEvidencePreview;

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    #[On('activity-updated')]
    public function refresh(): void
    {
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ActivityExecution::query()
                    ->with(['activity.drill.program'])
                    ->whereHas('activity', fn ($query) => $query->where('tipo', 'simulacro'))
                    ->whereDate('fecha_programada', now())
                    ->whereIn('estado', [
                        \App\Enums\ActivityState::PROGRAMADO,
                        \App\Enums\ActivityState::EN_PROCESO,
                        \App\Enums\ActivityState::EJECUTADO
                    ])
            )
            ->heading('Simulacros Programados para Hoy')
            ->columns([
                Tables\Columns\TextColumn::make('activity.drill.program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('activity.drill.nombre')
                    ->label('Simulacro')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activity.drill.escenario')
                    ->label('Escenario')
                    ->limit(30),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
            ])
            ->actions([
                \Filament\Actions\Action::make('ver_evidencias')
                    ->icon('heroicon-o-folder-open')
                    ->label('Evidencias')
                    ->color('info')
                    ->modalContent(fn (ActivityExecution $record) => view('filament.components.evidence-grid', [
                        'files' => $this->getEvidenceFiles($record),
                        'mode' => 'view',
                    ]))
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn () => \Filament\Actions\Action::make('cerrar')->label('Cerrar')->close())
                    ->visible(fn (ActivityExecution $record) => !empty($record->evidencia)),
                Action::make('iniciar')
                    ->label(fn (ActivityExecution $record) => match ($record->estado) {
                        \App\Enums\ActivityState::EJECUTADO => 'Completado',
                        \App\Enums\ActivityState::EN_PROCESO => 'Continuar',
                        default => 'Iniciar',
                    })
                    ->icon(fn (ActivityExecution $record) => match ($record->estado) {
                        \App\Enums\ActivityState::EJECUTADO => 'heroicon-o-check-circle',
                        default => 'heroicon-o-play',
                    })
                    ->color(fn (ActivityExecution $record) => match ($record->estado) {
                        \App\Enums\ActivityState::EJECUTADO => 'gray',
                        default => 'success',
                    })
                    ->disabled(fn (ActivityExecution $record) => $record->estado === \App\Enums\ActivityState::EJECUTADO)
                    ->button()
                    ->mountUsing(function (ActivityExecution $record) {
                        if ($record->estado === \App\Enums\ActivityState::PROGRAMADO) {
                            $record->update(['estado' => \App\Enums\ActivityState::EN_PROCESO]);
                        }
                    })
                    ->form([
                        \Filament\Forms\Components\Textarea::make('observacion')
                            ->label('Observaciones Generales')
                            ->rows(3)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\FileUpload::make('evidencia')
                            ->label('Evidencia (Informe/Fotos)')
                            ->disk('public') // Cambiamos a disco local temporalmente
                            ->directory('temp-uploads') // Directorio temporal local
                            ->visibility('private')
                            ->preserveFilenames()
                            ->multiple()
                            ->storeFileNamesIn('data->file_names')
                            ->columnSpanFull()
                            ->required(),

                        \Filament\Schemas\Components\Section::make('Detalles del Simulacro')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('data.participantes')
                                            ->label('Participantes')
                                            ->numeric(),
                                        \Filament\Forms\Components\TextInput::make('data.duracion_real')
                                            ->label('Duración Real (minutos)')
                                            ->numeric(),
                                        \Filament\Forms\Components\Textarea::make('data.conclusiones')
                                            ->label('Conclusiones')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->modalHeading('Ejecutar Simulacro')
                    ->modalSubmitActionLabel('Guardar')
                    ->action(function (ActivityExecution $record, array $data) {
                        \Illuminate\Support\Facades\Log::info('Inicio de acción guardar simulacro', ['record_id' => $record->id, 'data' => $data]);
                        
                        $evidenciaLocalPaths = $data['evidencia'] ?? [];
                        // Asegurar que sea array
                        if (is_string($evidenciaLocalPaths)) {
                            $evidenciaLocalPaths = [$evidenciaLocalPaths];
                        }
                        
                        $finalDrivePaths = [];

                        // Si hay archivos locales, moverlos a Google Drive
                        if (!empty($evidenciaLocalPaths)) {
                            $localDisk = \Illuminate\Support\Facades\Storage::disk('public');
                            $googleDisk = \Illuminate\Support\Facades\Storage::disk('google');

                            foreach ($evidenciaLocalPaths as $localPath) {
                                \Illuminate\Support\Facades\Log::info('Procesando archivo local de simulacro', ['path' => $localPath]);
                                
                                if ($localDisk->exists($localPath)) {
                                    $targetDirectory = \App\Services\DrivePathGenerator::generate($record);
                                    $fileName = basename($localPath);
                                    $targetPath = trim($targetDirectory, '/') . '/' . $fileName;
                                    
                                    try {
                                        if (!$googleDisk->exists($targetDirectory)) {
                                             $googleDisk->makeDirectory($targetDirectory);
                                        }
                                        
                                        $fileContents = $localDisk->get($localPath);
                                        $googleDisk->put($targetPath, $fileContents);
                                        
                                        if ($googleDisk->exists($targetPath)) {
                                            $finalDrivePaths[] = $targetPath;
                                            $localDisk->delete($localPath);
                                            \Illuminate\Support\Facades\Log::info('Archivo de simulacro subido exitosamente a Drive', ['drive_path' => $targetPath]);
                                        } else {
                                            \Illuminate\Support\Facades\Log::warning('El archivo de simulacro no parece existir en Drive tras la subida', ['drive_path' => $targetPath]);
                                        }
                                    } catch (\Exception $e) {
                                        \Illuminate\Support\Facades\Log::error("Error moviendo archivo de simulacro {$localPath}: " . $e->getMessage());
                                    }
                                } else {
                                    \Illuminate\Support\Facades\Log::warning('Archivo local de simulacro no encontrado', ['path' => $localPath]);
                                }
                            }
                        } else {
                            \Illuminate\Support\Facades\Log::info('No hay evidencias de simulacro para subir (evidenciaLocalPaths vacío)');
                        }
                        
                        if (!empty($evidenciaLocalPaths) && empty($finalDrivePaths)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Advertencia de Carga')
                                ->body('Se detectaron archivos pero no se pudieron transferir a Google Drive. Revise los logs del sistema.')
                                ->warning()
                                ->send();
                        }

                        $record->update([
                            'observacion' => $data['observacion'] ?? null,
                            'evidencia' => !empty($finalDrivePaths) ? $finalDrivePaths : null,
                            'estado' => \App\Enums\ActivityState::EJECUTADO,
                            'fecha_ejecucion_real' => now(),
                            'data' => $data['data'] ?? [],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Simulacro Ejecutado')
                            ->success()
                            ->send();
                        \Illuminate\Support\Facades\Log::info('--- FIN GUARDADO DRILL FOR TODAY ---');
                        $this->dispatch('activity-updated');
                    }),
                \Filament\Actions\ViewAction::make()
                    ->label('Ver')
                    ->modalHeading('Detalles de la Ejecución')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('activity.drill.nombre')
                            ->label('Simulacro'),
                        \Filament\Forms\Components\TextInput::make('fecha_programada')
                            ->label('Fecha Programada'),
                        \Filament\Forms\Components\TextInput::make('estado')
                            ->label('Estado'),
                        \Filament\Forms\Components\Textarea::make('observacion')
                            ->label('Observaciones'),
                    ]),
            ])
            ->emptyStateHeading('No hay simulacros programados para hoy')
            ->paginated(false);
    }
}
