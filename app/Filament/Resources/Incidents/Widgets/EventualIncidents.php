<?php

namespace App\Filament\Resources\Incidents\Widgets;

use App\Models\Incident;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class EventualIncidents extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Incident::query()
            ->whereIn('estado', ['programado', 'en_proceso'])
            ->whereHas('activity', function ($q) {
                $q->where('frecuencia', 'eventual');
            })->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Incident::query()
                    ->whereIn('estado', ['programado', 'en_proceso'])
                    ->whereHas('activity', function ($q) {
                        $q->where('frecuencia', 'eventual');
                    })
            )
            ->heading('Incidentes Eventuales / No Programados')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Incidente')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activity.frecuencia')
                    ->label('Frecuencia')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('fecha_ocurrencia')
                    ->label('Fecha Ocurrencia')
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
                    ->modalHeading('Iniciar Gestión de Incidente')
                    ->modalDescription('¿Estás seguro de que deseas iniciar la gestión de este incidente? El estado cambiará a "En Proceso" o "Ejecutado".')
                    ->modalSubmitActionLabel('Sí, iniciar')
                    ->action(function ($record) {
                        if ($record->activity) {
                            $record->activity->increment('ejecuciones_realizadas');
                            
                            if ($record->activity->ejecuciones_realizadas >= $record->activity->veces_al_anio) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                                $record->update([
                                    'estado' => 'ejecutado',
                                ]);
                                $message = 'Incidente procesado (Meta alcanzada)';
                            } else {
                                $record->activity->update(['estado' => 'en_proceso']);
                                $record->update([
                                    'estado' => 'en_proceso',
                                ]);
                                $message = "Acción registrada.";
                            }
                        } else {
                            $record->update([
                                'estado' => 'en_proceso',
                            ]);
                            $message = 'Incidente pasado a investigación';
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
