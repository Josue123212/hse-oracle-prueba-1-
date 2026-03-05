<?php

namespace App\Filament\Widgets;

use App\Models\ActivityExecution;
use App\Enums\ActivityState;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class OverdueActivitiesWidget extends BaseWidget
{
    protected string $view = 'filament.widgets.overdue-activities-widget';

    protected static ?int $sort = 2; // Position after the chart (same row)
    
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Ejecuciones Vencidas';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ActivityExecution::query()
                    ->with(['activity', 'activity.responsable'])
                    // El GlobalScope (ProgramScope) se encarga de filtrar por programa si está en sesión
                    ->whereDate('fecha_programada', '<', now()->startOfDay())
                    ->whereIn('estado', [
                        ActivityState::PROGRAMADO,
                        ActivityState::EN_PROCESO,
                        ActivityState::NO_CUMPLIO
                    ])
                    ->orderBy('fecha_programada', 'asc')
            )
            ->heading(null)
            ->columns([
                Tables\Columns\TextColumn::make('activity.nombre')
                    ->label('Actividad')
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-o-exclamation-circle')
                    ->iconColor('danger')
                    ->tooltip('Actividad Vencida'),

                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Fecha Programada')
                    ->date('d/m/Y')
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
            ])
            ->paginated(false)
            ->actions([
                \Filament\Actions\ViewAction::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->button()
                    ->infolist([
                        \Filament\Schemas\Components\Section::make('Detalles')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('activity.nombre')
                                            ->label('Actividad')
                                            ->weight('bold'),
                                        \Filament\Infolists\Components\TextEntry::make('activity.tipo')
                                            ->label('Tipo')
                                            ->formatStateUsing(fn ($state) => ucfirst($state))
                                            ->badge(),
                                        \Filament\Infolists\Components\TextEntry::make('fecha_programada')
                                            ->label('Fecha Programada')
                                            ->date('d/m/Y')
                                            ->color('danger'),
                                        \Filament\Infolists\Components\TextEntry::make('activity.responsable.nombre')
                                            ->label('Responsable')
                                            ->placeholder('Sin asignar'),
                                        \Filament\Infolists\Components\TextEntry::make('activity.descripcion')
                                            ->label('Descripción')
                                            ->columnSpanFull()
                                            ->placeholder('Sin descripción'),
                                    ]),
                            ]),
                    ]),
                Action::make('regularizar')
                    ->label('Regularizar')
                    ->icon('heroicon-o-play')
                    ->color('danger')
                    ->button()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('observacion')
                            ->label('Observación de Regularización')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        \Filament\Forms\Components\FileUpload::make('evidencia')
                            ->label('Evidencia (Archivo)')
                            ->disk('public')
                            ->directory('temp-uploads')
                            ->visibility('private')
                            ->preserveFilenames()
                            ->columnSpanFull(),
                    ])
                    ->modalHeading('Regularizar Actividad Vencida')
                    ->modalSubmitActionLabel('Ejecutar Ahora')
                    ->action(function (ActivityExecution $record, array $data) {
                        // Lógica idéntica a ActivitiesForToday: subir evidencia y actualizar estado
                        \Illuminate\Support\Facades\Log::info('--- INICIO REGULARIZACION ACTIVIDAD VENCIDA ---');
                        
                        $evidenciaLocalPath = $data['evidencia'] ?? null;
                        if (is_array($evidenciaLocalPath)) {
                            $evidenciaLocalPath = reset($evidenciaLocalPath);
                        }

                        $finalDrivePath = null;

                        if ($evidenciaLocalPath) {
                            $targetDirectory = \App\Services\DrivePathGenerator::generate($record->activity);
                            $fileName = basename($evidenciaLocalPath);
                            $targetPath = trim($targetDirectory, '/') . '/' . $fileName;
                            
                            try {
                                $localDisk = \Illuminate\Support\Facades\Storage::disk('public');
                                $googleDisk = \Illuminate\Support\Facades\Storage::disk('google');
                                
                                if ($localDisk->exists($evidenciaLocalPath)) {
                                    if (!$googleDisk->exists($targetDirectory)) {
                                        $googleDisk->makeDirectory($targetDirectory);
                                    }
                                    
                                    $fileContents = $localDisk->get($evidenciaLocalPath);
                                    $googleDisk->put($targetPath, $fileContents);
                                    
                                    if ($googleDisk->exists($targetPath)) {
                                        $finalDrivePath = $targetPath;
                                        $localDisk->delete($evidenciaLocalPath);
                                    }
                                }
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("OverdueWidget: Error subiendo archivo: " . $e->getMessage());
                            }
                        }

                        $record->update([
                            'observacion' => $data['observacion'] ?? null,
                            'evidencia' => $finalDrivePath,
                            'estado' => ActivityState::EJECUTADO,
                            'fecha_ejecucion_real' => now(),
                        ]);
                        
                        // Recalcular próxima ejecución
                        if ($record->activity) {
                            app(\App\Services\ActivityService::class)->calculateNextExecution($record->activity);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Actividad Regularizada')
                            ->body('La actividad ha sido marcada como ejecutada con fecha de hoy.')
                            ->success()
                            ->send();
                            
                        \Illuminate\Support\Facades\Log::info('--- FIN REGULARIZACION ACTIVIDAD VENCIDA ---');
                    }),
            ]);
    }
}
