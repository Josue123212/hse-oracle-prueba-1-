<?php

namespace App\Filament\Resources\Promotions\Widgets;

use App\Models\Activity;
use App\Models\ActivityExecution;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

class EventualPromotions extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Actividades Eventuales - Promociones';

    protected ?string $pollingInterval = '30s';

    #[On('activity-executed')]
    public function refresh(): void
    {
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->where('tipo', 'promocion')
                    ->where('frecuencia', 'eventual')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Actividad')
                    ->description(fn (Activity $record) => $record->descripcion ?? 'Sin descripción')
                    ->weight('bold')
                    ->wrap(),
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
                            ->label('Informe de la Campaña')
                            ->rows(3)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\FileUpload::make('evidencia')
                            ->label('Evidencia (Archivo)')
                            ->disk('public') // Cambiamos a disco local temporalmente
                            ->directory('temp-uploads') // Directorio temporal local
                            ->visibility('private')
                            ->columnSpanFull(),

                        \Filament\Schemas\Components\Section::make('Detalles de la Promoción')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('data.alcance_real')
                                            ->label('Personas Alcanzadas')
                                            ->numeric(),
                                        \Filament\Forms\Components\TextInput::make('data.canales_utilizados')
                                            ->label('Canales Utilizados')
                                            ->placeholder('Ej: Email, Intranet, Cartelera'),
                                    ]),
                            ]),
                    ])
                    ->modalHeading('Ejecutar Campaña Eventual')
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
                            ->title('Campaña Eventual Ejecutada')
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
