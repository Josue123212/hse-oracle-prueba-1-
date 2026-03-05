<?php

namespace App\Filament\Resources\Incidents\Widgets;

use App\Models\Activity;
use App\Models\ActivityExecution;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;

class EventualIncidents extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Actividades Eventuales - Incidentes';

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
                    ->where(function ($query) {
                        $query->where('tipo', 'incidente')
                              ->orWhereHas('incident');
                    })
                    ->where('frecuencia', 'eventual')
            )
            ->heading('Actividades Eventuales - Incidentes')
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->recordAction('view')
            ->columns([
                Stack::make([
                    Tables\Columns\TextColumn::make('nombre')
                        ->label('Actividad')
                        ->weight(FontWeight::Bold)
                        ->size('lg')
                        ->searchable(),
                    
                    Tables\Columns\TextColumn::make('descripcion')
                        ->label('Descripción')
                        ->limit(50)
                        ->color('gray'),

                    Split::make([
                        Tables\Columns\TextColumn::make('frecuencia')
                            ->badge()
                            ->color('warning'),
                    ]),
                ])->space(3),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                Action::make('iniciar')
                    ->label(fn (Activity $record) => $this->hasExecutedToday($record) ? 'Completado Hoy' : 'Iniciar')
                    ->icon(fn (Activity $record) => $this->hasExecutedToday($record) ? 'heroicon-o-check-circle' : 'heroicon-o-play')
                    ->color(fn (Activity $record) => $this->hasExecutedToday($record) ? 'gray' : 'success')
                    ->disabled(fn (Activity $record) => $this->hasExecutedToday($record))
                    ->button()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('observacion')
                            ->label('Informe de Investigación')
                            ->rows(3)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\FileUpload::make('evidencia')
                            ->label('Evidencia (Archivo)')
                            ->disk('public') // Cambiamos a disco local temporalmente
                            ->directory('temp-uploads') // Directorio temporal local
                            ->visibility('private')
                            ->columnSpanFull(),

                        \Filament\Schemas\Components\Section::make('Detalles de la Investigación')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Forms\Components\Textarea::make('data.causa_raiz')
                                            ->label('Causa Raíz'),
                                        \Filament\Forms\Components\Textarea::make('data.acciones_correctivas')
                                            ->label('Acciones Correctivas'),
                                    ]),
                            ]),
                    ])
                    ->modalHeading('Ejecutar Investigación Eventual')
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
                            ->title('Investigación Eventual Registrada')
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
