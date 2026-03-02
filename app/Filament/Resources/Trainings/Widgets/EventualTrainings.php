<?php

namespace App\Filament\Resources\Trainings\Widgets;

use App\Models\Activity;
use App\Models\ActivityExecution;
use App\Models\Training;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

class EventualTrainings extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Actividades Eventuales - Capacitaciones';

    protected ?string $pollingInterval = '30s';

    #[On('activity-executed')]
    public function refresh(): void
    {
    }

    public static function canView(): bool
    {
        return Activity::query()
            ->where('tipo', 'capacitacion')
            ->where('frecuencia', 'eventual')
            ->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->with(['training.program', 'training.responsable'])
                    ->where('tipo', 'capacitacion')
                    ->where('frecuencia', 'eventual')
            )
            ->columns([
                Tables\Columns\TextColumn::make('training.program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('training.tema')
                    ->label('Tema')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('training.responsable.nombre')
                    ->label('Cargo Responsable')
                    ->placeholder('Por definir'),
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

                        \Filament\Schemas\Components\Section::make('Detalles de Capacitación')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('data.asistentes_reales')
                                            ->label('Asistentes Reales')
                                            ->numeric(),
                                        \Filament\Forms\Components\Textarea::make('data.comentarios')
                                            ->label('Comentarios Adicionales'),
                                    ]),
                            ]),
                    ])
                    ->modalHeading('Ejecutar Capacitación Eventual')
                    ->modalSubmitActionLabel('Guardar')
                    ->action(function (Activity $record, array $data) {
                        $evidenciaLocalPath = $data['evidencia'] ?? null;
                        $finalDrivePath = null;
                        
                        if ($evidenciaLocalPath) {
                            $localDisk = \Illuminate\Support\Facades\Storage::disk('public');
                            $googleDisk = \Illuminate\Support\Facades\Storage::disk('google');
                            
                            if ($localDisk->exists($evidenciaLocalPath)) {
                                $targetDirectory = \App\Services\DrivePathGenerator::generate($record);
                                $fileName = basename($evidenciaLocalPath);
                                $targetPath = trim($targetDirectory, '/') . '/' . $fileName;
                                
                                try {
                                    if (!$googleDisk->exists($targetDirectory)) {
                                         $googleDisk->makeDirectory($targetDirectory);
                                    }
                                    
                                    $fileContents = $localDisk->get($evidenciaLocalPath);
                                    $googleDisk->put($targetPath, $fileContents);
                                    
                                    if ($googleDisk->exists($targetPath)) {
                                        $finalDrivePath = $targetPath;
                                        $localDisk->delete($evidenciaLocalPath);
                                    }
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error("Error moviendo archivo: " . $e->getMessage());
                                }
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
                            ->title('Capacitación Eventual Ejecutada')
                            ->success()
                            ->send();

                        $this->dispatch('activity-executed');
                    }),
            ])
            ->paginated(false);
    }

    protected function hasExecutedToday(Activity $activity): bool
    {
        return $activity->executions()
            ->whereDate('created_at', now())
            ->exists();
    }
}
