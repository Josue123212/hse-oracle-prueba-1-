<?php

namespace App\Filament\Resources\OperationalControls\Widgets;

use App\Models\OperationalControl;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Livewire\Attributes\On;

class OperationalControlsForToday extends BaseWidget
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
        return $table
            ->query(
                OperationalControl::query()
                    ->whereIn('estado', ['pendiente', 'en_proceso'])
                    ->where(function ($query) {
                        $today = now();
                        $query->where(function ($q) use ($today) {
                                $q->whereIn('frecuencia', ['unico', 'eventual'])
                                ->whereDate('fecha_programada', $today);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'diario')
                                ->whereDate('fecha_programada', '<=', $today);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'semanal')
                                ->whereDate('fecha_programada', '<=', $today)
                                ->whereRaw("EXTRACT(DOW FROM fecha_programada) = ?", [$today->dayOfWeek]);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'mensual')
                                ->whereDate('fecha_programada', '<=', $today)
                                ->whereRaw("EXTRACT(DAY FROM fecha_programada) = ?", [$today->day]);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'trimestral')
                                ->whereDate('fecha_programada', '<=', $today)
                                ->whereRaw("EXTRACT(DAY FROM fecha_programada) = ?", [$today->day])
                                ->whereRaw("MOD((EXTRACT(YEAR FROM AGE(?, fecha_programada)) * 12 + EXTRACT(MONTH FROM AGE(?, fecha_programada))), 3) = 0", [$today, $today]);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'semestral')
                                ->whereDate('fecha_programada', '<=', $today)
                                ->whereRaw("EXTRACT(DAY FROM fecha_programada) = ?", [$today->day])
                                ->whereRaw("MOD((EXTRACT(YEAR FROM AGE(?, fecha_programada)) * 12 + EXTRACT(MONTH FROM AGE(?, fecha_programada))), 6) = 0", [$today, $today]);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'anual')
                                ->whereDate('fecha_programada', '<=', $today)
                                ->whereRaw("EXTRACT(DAY FROM fecha_programada) = ?", [$today->day])
                                ->whereRaw("EXTRACT(MONTH FROM fecha_programada) = ?", [$today->month]);
                        });
                    })
            )
            ->heading('Controles Operacionales Pendientes para Hoy')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('nombre_proceso')
                    ->label('Proceso')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parametro')
                    ->label('Parámetro'),
                Tables\Columns\TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function (OperationalControl $record): string {
                        return "{$record->ejecuciones_realizadas} / {$record->veces_al_anio}";
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('valor_esperado')
                    ->label('Esperado')
                    ->badge()
                    ->color('info'),
            ])
            ->actions([
                Action::make('registrar_control')
                    ->label(function (OperationalControl $record) {
                        return "Registrar Medición ({$record->ejecuciones_realizadas}/{$record->veces_al_anio})";
                    })
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning') // Yellow to indicate action needed
                    ->button()
                    ->form([
                        TextInput::make('valor_medido')
                            ->label('Valor Medido / Resultado')
                            ->required()
                            ->placeholder('Ej. 85 dB, 100%, Cumple'),
                        Select::make('estado')
                            ->label('Estado Final')
                            ->options([
                                'conforme' => 'Conforme',
                                'no_conforme' => 'No Conforme',
                            ])
                            ->required()
                            ->default('conforme'),
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(2),
                    ])
                    ->action(function (OperationalControl $record, array $data) {
                        // Update child record
                        $record->update([
                            'valor_medido' => $data['valor_medido'],
                            'estado' => $data['estado'], 
                            'observaciones' => $data['observaciones'],
                        ]);

                        // Increment executions (will sync to parent via model events)
                        $record->increment('ejecuciones_realizadas');
                        
                        // Logic to handle completion vs in_progress
                        if ($record->ejecuciones_realizadas >= $record->veces_al_anio) {
                            // Sync parent status if needed (though model event handles some sync, specific status logic might be needed)
                            // The model event in OperationalControl.php syncs columns but doesn't have the status logic "ejecutado" vs "en_proceso"
                            // However, Activity::updateStatusBasedOnProgress() exists but is not automatically called by simple update?
                            // Let's rely on the fact that if we update parent, we might want to set status explicitly here or let the parent handle it.
                            // But OperationalControl.php booted() only syncs columns.
                            // So we should probably update parent status here or add logic to OperationalControl model.
                            
                            // For now, let's keep the logic here but use local data
                            if ($record->activity) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                            }
                            $message = 'Control operacional completado (Meta alcanzada)';
                        } else {
                            if ($record->activity) {
                                $record->activity->update(['estado' => 'en_proceso']);
                            }
                            // Keep child in list for next run
                            $record->update(['estado' => 'en_proceso']);
                            $message = "Medición registrada. Progreso: {$record->ejecuciones_realizadas}/{$record->veces_al_anio}";
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No hay controles operacionales pendientes')
            ->emptyStateDescription('Todos los controles del día han sido registrados.')
            ->paginated(false);
    }
}
