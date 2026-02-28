<?php

namespace App\Filament\Resources\OperationalControls\Widgets;

use App\Models\OperationalControl;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class EventualOperationalControls extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return OperationalControl::query()
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->whereHas('activity', function ($q) {
                $q->where('frecuencia', 'eventual');
            })->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OperationalControl::query()
                    ->whereIn('estado', ['pendiente', 'en_proceso'])
                    ->whereHas('activity', function ($q) {
                        $q->where('frecuencia', 'eventual');
                    })
            )
            ->heading('Controles Operacionales Eventuales / No Programados')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('nombre_proceso')
                    ->label('Proceso')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activity.frecuencia')
                    ->label('Frecuencia')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Fecha Programada')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function ($record): string {
                        if ($record->activity) {
                            return "{$record->activity->ejecuciones_realizadas} / {$record->activity->veces_al_anio}";
                        }
                        return "N/A";
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
            ])
            ->actions([
                Action::make('iniciar')
                    ->label(function ($record) {
                        $progress = "N/A";
                        if ($record->activity) {
                            $progress = "{$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}";
                        }
                        return "Iniciar ($progress)";
                    })
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Iniciar Control Operacional')
                    ->modalDescription(function ($record) {
                        $progress = "";
                        if ($record->activity) {
                            $progress = "Progreso actual: {$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}.";
                        }
                        return "¿Estás seguro de que deseas iniciar este control? $progress El estado cambiará a 'Conforme' o 'En Proceso' según corresponda.";
                    })
                    ->modalSubmitActionLabel('Sí, iniciar')
                    ->action(function ($record) {
                        if ($record->activity) {
                            $record->activity->increment('ejecuciones_realizadas');
                            
                            if ($record->activity->ejecuciones_realizadas >= $record->activity->veces_al_anio) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                                $record->update([
                                    'estado' => 'conforme',
                                ]);
                                $message = 'Control completado (Meta alcanzada)';
                            } else {
                                $record->activity->update(['estado' => 'en_proceso']);
                                $record->update([
                                    'estado' => 'en_proceso',
                                ]);
                                $message = "Ejecución registrada. Progreso: {$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}";
                            }
                        } else {
                            $record->update(['estado' => 'conforme']);
                            $message = 'Control iniciado';
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
