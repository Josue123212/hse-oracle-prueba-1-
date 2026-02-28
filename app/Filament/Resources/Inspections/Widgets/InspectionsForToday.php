<?php

namespace App\Filament\Resources\Inspections\Widgets;

use App\Models\Inspection;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

class InspectionsForToday extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '2s';

    #[On('activity-updated')]
    public function refresh(): void
    {
    }

    public function table(Table $table): Table
    {
        // Auto-expire logic if needed (optional, keeping it simple for now or copying Audit logic if consistent)
        // For now, focusing on the "Start" feature.

        return $table
            ->query(
                Inspection::query()
                    ->whereIn('estado', ['programado', 'en_proceso'])
                    ->where(function ($query) {
                        $query->whereHas('activity', function ($q) {
                            $today = now();
                            $q->where(function ($sq) use ($today) {
                                 $sq->whereIn('frecuencia', ['unico', 'eventual'])
                                   ->whereDate('fecha_inicio', $today);
                            });
                            $q->orWhere(function ($sq) use ($today) {
                                 $sq->where('frecuencia', 'diario')
                                   ->whereDate('fecha_inicio', '<=', $today);
                            });
                            $q->orWhere(function ($sq) use ($today) {
                                  $sq->where('frecuencia', 'semanal')
                                    ->whereDate('fecha_inicio', '<=', $today)
                                    ->whereRaw("EXTRACT(DOW FROM fecha_inicio) = ?", [$today->dayOfWeek]);
                            });
                            $q->orWhere(function ($sq) use ($today) {
                                  $sq->where('frecuencia', 'mensual')
                                    ->whereDate('fecha_inicio', '<=', $today)
                                    ->whereRaw("EXTRACT(DAY FROM fecha_inicio) = ?", [$today->day]);
                            });
                            $q->orWhere(function ($sq) use ($today) {
                                  $sq->where('frecuencia', 'trimestral')
                                    ->whereDate('fecha_inicio', '<=', $today)
                                    ->whereRaw("EXTRACT(DAY FROM fecha_inicio) = ?", [$today->day])
                                    ->whereRaw("MOD((EXTRACT(YEAR FROM AGE(?, fecha_inicio)) * 12 + EXTRACT(MONTH FROM AGE(?, fecha_inicio))), 3) = 0", [$today, $today]);
                            });
                            $q->orWhere(function ($sq) use ($today) {
                                  $sq->where('frecuencia', 'semestral')
                                    ->whereDate('fecha_inicio', '<=', $today)
                                    ->whereRaw("EXTRACT(DAY FROM fecha_inicio) = ?", [$today->day])
                                    ->whereRaw("MOD((EXTRACT(YEAR FROM AGE(?, fecha_inicio)) * 12 + EXTRACT(MONTH FROM AGE(?, fecha_inicio))), 6) = 0", [$today, $today]);
                            });
                            $q->orWhere(function ($sq) use ($today) {
                                  $sq->where('frecuencia', 'anual')
                                    ->whereDate('fecha_inicio', '<=', $today)
                                    ->whereRaw("EXTRACT(DAY FROM fecha_inicio) = ?", [$today->day])
                                    ->whereRaw("EXTRACT(MONTH FROM fecha_inicio) = ?", [$today->month]);
                            });
                        })
                        ->orWhere(function ($q) {
                            $q->doesntHave('activity')
                              ->whereDate('fecha_programada', now()->toDateString());
                        });
                    })
            )
            ->heading('Inspecciones Programadas para Hoy')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Inspección')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsable.name')
                    ->label('Responsable')
                    ->placeholder('Sin asignar'),
                Tables\Columns\TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function (Inspection $record): string {
                        // We need to access the parent activity to get these values
                        // assuming the relation is 'activity'
                        if ($record->activity) {
                            return "{$record->activity->ejecuciones_realizadas} / {$record->activity->veces_al_anio}";
                        }
                        return "N/A";
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Fecha')
                    ->formatStateUsing(fn () => now()->format('d/m/Y')),
            ])
            ->actions([
                Action::make('iniciar')
                    ->label(function (Inspection $record) {
                        $progress = "N/A";
                        if ($record->activity) {
                            $progress = "{$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}";
                        }
                        return "Iniciar Inspección ($progress)";
                    })
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Iniciar Inspección')
                    ->modalDescription(function (Inspection $record) {
                        $progress = "";
                        if ($record->activity) {
                            $progress = "Progreso actual: {$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}.";
                        }
                        return "¿Confirmas el inicio de esta inspección? $progress";
                    })
                    ->modalSubmitActionLabel('Sí, ejecutar')
                    ->action(function (Inspection $record) {
                        // Update Parent Activity Progress
                        if ($record->activity) {
                            $record->activity->increment('ejecuciones_realizadas');
                            
                            if ($record->activity->ejecuciones_realizadas >= $record->activity->veces_al_anio) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                                
                                // Also update the inspection record itself
                                $record->update([
                                    'estado' => 'ejecutado',
                                ]);
                                $message = 'Inspección completada (Meta alcanzada)';
                            } else {
                                $record->activity->update(['estado' => 'en_proceso']);
                                // Inspection record stays in 'programado' or maybe 'en_proceso' too?
                                // Usually the child record represents ONE instance. 
                                // If the child record is "The Monthly Inspection", then it should be marked done.
                                // BUT if we are reusing the same record, we just update the parent.
                                // Based on user request "0/4 -> 1/4", it implies reusing the record or cumulative tracking.
                                // Let's assume we update the child status to 'en_proceso' as well if not finished, 
                                // OR we keep it open until fully done.
                                // Given the request "change to executed ONLY when 4/4", 
                                // I will set child status to 'en_proceso' as well.
                                $record->update([
                                    'estado' => 'en_proceso',
                                ]);
                                $message = "Ejecución registrada. Progreso: {$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}";
                            }
                        } else {
                            // Fallback for orphaned records (shouldn't happen with new constraints)
                            $record->update([
                                'estado' => 'ejecutado',
                            ]);
                            $message = 'Inspección iniciada y registrada';
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No hay inspecciones pendientes para hoy')
            ->emptyStateDescription('Todo está al día.')
            ->paginated(false);
    }
}
