<?php

namespace App\Filament\Resources\Trainings\Widgets;

use App\Models\Training;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

class TrainingsForToday extends BaseWidget
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
                Training::query()
                    ->whereDate('fecha_programada', now())
                    ->whereIn('estado', ['programado', 'en_proceso'])
            )
            ->heading('Capacitaciones Programadas para Hoy')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('tema')
                    ->label('Tema')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activity.frecuencia')
                    ->label('Frecuencia')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('hora_inicio')
                    ->label('Hora')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('responsable.name')
                    ->label('Responsable')
                    ->placeholder('Por definir'),
            ])
            ->actions([
                Action::make('registrar_asistencia')
                    ->label('Iniciar / Asistencia')
                    ->icon('heroicon-o-user-group')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Iniciar Capacitación')
                    ->modalDescription('Esto marcará la capacitación como realizada. ¿Deseas continuar?')
                    ->modalSubmitActionLabel('Sí, ejecutar')
                    ->action(function (Training $record) {
                        if ($record->activity) {
                            $record->activity->increment('ejecuciones_realizadas');
                            
                            if ($record->activity->ejecuciones_realizadas >= $record->activity->veces_al_anio) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                                $record->update([
                                    'estado' => 'ejecutado',
                                ]);
                                $message = 'Capacitación completada (Meta alcanzada)';
                            } else {
                                $record->activity->update(['estado' => 'en_proceso']);
                                $record->update([
                                    'estado' => 'en_proceso',
                                ]);
                                $message = "Ejecución registrada.";
                            }
                        } else {
                            $record->update([
                                'estado' => 'ejecutado',
                            ]);
                            $message = 'Capacitación iniciada';
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No hay capacitaciones programadas para hoy')
            ->paginated(false);
    }
}
