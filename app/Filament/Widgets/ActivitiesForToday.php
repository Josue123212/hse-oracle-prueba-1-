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
                    ->whereHas('executions', function ($query) {
                        $query->whereDate('fecha_programada', now());
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
