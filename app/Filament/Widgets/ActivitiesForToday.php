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
    protected int | string | array $columnSpan = '1/2';

    protected static ?int $sort = 1;

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
                Activity::query()
                    ->whereIn('estado', ['programado', 'en_proceso'])
                    ->where(function ($query) {
                        $today = now();
                        
                        // 1. Unico / Eventual: Started on or before today
                        $query->where(function ($q) use ($today) {
                            $q->whereIn('frecuencia', ['unico', 'eventual'])
                              ->whereDate('fecha_inicio', '<=', $today);
                        });
                        
                        // 2. Diario: Started on or before today
                        $query->orWhere(function ($q) use ($today) {
                            $q->where('frecuencia', 'diario')
                              ->whereDate('fecha_inicio', '<=', $today);
                        });
                        
                        // 3. Semanal: Started on or before today AND same day of week
                        $query->orWhere(function ($q) use ($today) {
                             $q->where('frecuencia', 'semanal')
                               ->whereDate('fecha_inicio', '<=', $today)
                               ->whereRaw("EXTRACT(DOW FROM fecha_inicio) = ?", [$today->dayOfWeek]);
                        });
                        
                        // 4. Mensual: Started on or before today AND same day of month
                        $query->orWhere(function ($q) use ($today) {
                             $q->where('frecuencia', 'mensual')
                               ->whereDate('fecha_inicio', '<=', $today)
                               ->whereRaw("EXTRACT(DAY FROM fecha_inicio) = ?", [$today->day]);
                        });
                        
                        // 5. Trimestral: Started on or before today AND same day of month AND month diff % 3 == 0
                        $query->orWhere(function ($q) use ($today) {
                             $q->where('frecuencia', 'trimestral')
                               ->whereDate('fecha_inicio', '<=', $today)
                               ->whereRaw("EXTRACT(DAY FROM fecha_inicio) = ?", [$today->day])
                               ->whereRaw("MOD((EXTRACT(YEAR FROM AGE(?, fecha_inicio)) * 12 + EXTRACT(MONTH FROM AGE(?, fecha_inicio))), 3) = 0", [$today, $today]);
                        });

                        // 6. Semestral: Started on or before today AND same day of month AND month diff % 6 == 0
                        $query->orWhere(function ($q) use ($today) {
                             $q->where('frecuencia', 'semestral')
                               ->whereDate('fecha_inicio', '<=', $today)
                               ->whereRaw("EXTRACT(DAY FROM fecha_inicio) = ?", [$today->day])
                               ->whereRaw("MOD((EXTRACT(YEAR FROM AGE(?, fecha_inicio)) * 12 + EXTRACT(MONTH FROM AGE(?, fecha_inicio))), 6) = 0", [$today, $today]);
                        });

                        // 7. Anual: Started on or before today AND same day and month
                        $query->orWhere(function ($q) use ($today) {
                             $q->where('frecuencia', 'anual')
                               ->whereDate('fecha_inicio', '<=', $today)
                               ->whereRaw("EXTRACT(DAY FROM fecha_inicio) = ?", [$today->day])
                               ->whereRaw("EXTRACT(MONTH FROM fecha_inicio) = ?", [$today->month]);
                        });
                    })
            )
            ->heading(null)
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Actividad')
                    ->weight('bold')
                    ->wrap(),
                Tables\Columns\TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function (Activity $record): string {
                        return "{$record->ejecuciones_realizadas} / {$record->veces_al_anio}";
                    })
                    ->badge()
                    ->color(fn (string $state): string => 'info')
                    ->alignCenter(),
            ])
            ->actions([
                Action::make('ejecutar_actividad')
                    ->label(fn (Activity $record) => "Registrar Ejecución (" . ($record->ejecuciones_realizadas) . "/" . $record->veces_al_anio . ")")
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Ejecución')
                    ->modalDescription(fn (Activity $record) => "Se registrará una nueva ejecución. Progreso actual: {$record->ejecuciones_realizadas}/{$record->veces_al_anio}. ¿Continuar?")
                    ->action(function (Activity $record) {
                        $record->increment('ejecuciones_realizadas');
                        
                        if ($record->ejecuciones_realizadas >= $record->veces_al_anio) {
                            $record->update([
                                'estado' => 'ejecutado',
                                'fecha_ejecucion' => now(),
                            ]);
                            $message = 'Actividad completada exitosamente (Meta alcanzada)';
                        } else {
                            $record->update([
                                'estado' => 'en_proceso',
                            ]);
                            $message = "Ejecución registrada. Progreso: {$record->ejecuciones_realizadas}/{$record->veces_al_anio}";
                        }

                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No hay actividades pendientes para hoy')
            ->paginated(false);
    }
}
