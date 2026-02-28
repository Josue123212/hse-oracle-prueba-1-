<?php

namespace App\Filament\Resources\Trainings\Widgets;

use App\Models\Training;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class EventualTrainings extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Training::query()
            ->whereIn('estado', ['programado', 'en_proceso'])
            ->whereHas('activity', function ($q) {
                $q->where('frecuencia', 'eventual');
            })->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Training::query()
                    ->whereIn('estado', ['programado', 'en_proceso'])
                    ->whereHas('activity', function ($q) {
                        $q->where('frecuencia', 'eventual');
                    })
            )
            ->heading('Capacitaciones Eventuales / No Programadas')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('tema')
                    ->label('Tema')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsable.name')
                    ->label('Responsable'),
                Tables\Columns\TextColumn::make('activity.frecuencia')
                    ->label('Frecuencia')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Fecha Programada')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
            ])
            ->actions([
                Action::make('iniciar')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Iniciar Capacitación')
                    ->modalDescription('¿Estás seguro de que deseas iniciar esta capacitación? El estado cambiará a "Ejecutado" o "En Proceso" según corresponda.')
                    ->modalSubmitActionLabel('Sí, iniciar')
                    ->action(function ($record) {
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
                                'estado' => 'en_proceso',
                            ]);
                            $message = 'Capacitación iniciada';
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->paginated(false);
    }
}
