<?php

namespace App\Filament\Resources\Inspections\Widgets;

use App\Models\Inspection;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class EventualInspections extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Inspection::query()
            ->whereIn('estado', ['programado', 'en_proceso'])
            ->whereHas('activity', function ($q) {
                $q->where('frecuencia', 'eventual');
            })->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Inspection::query()
                    ->whereIn('estado', ['programado', 'en_proceso'])
                    ->whereHas('activity', function ($q) {
                        $q->where('frecuencia', 'eventual');
                    })
            )
            ->heading('Inspecciones Eventuales / No Programadas')
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
                    ->label('Responsable'),
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
                    ->modalHeading('Iniciar Inspección')
                    ->modalDescription(function ($record) {
                        $progress = "";
                        if ($record->activity) {
                            $progress = "Progreso actual: {$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}.";
                        }
                        return "¿Estás seguro de que deseas iniciar esta inspección? $progress El estado cambiará a 'Ejecutado' o 'En Proceso' según corresponda.";
                    })
                    ->modalSubmitActionLabel('Sí, iniciar')
                    ->action(function ($record) {
                        if ($record->activity) {
                            $record->activity->increment('ejecuciones_realizadas');
                            
                            if ($record->activity->ejecuciones_realizadas >= $record->activity->veces_al_anio) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                                $record->update([
                                    'estado' => 'ejecutado',
                                ]);
                                $message = 'Inspección completada (Meta alcanzada)';
                            } else {
                                $record->activity->update(['estado' => 'en_proceso']);
                                $record->update([
                                    'estado' => 'en_proceso',
                                ]);
                                $message = "Ejecución registrada. Progreso: {$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}";
                            }
                        } else {
                            $record->update(['estado' => 'ejecutado']);
                            $message = 'Inspección iniciada';
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
