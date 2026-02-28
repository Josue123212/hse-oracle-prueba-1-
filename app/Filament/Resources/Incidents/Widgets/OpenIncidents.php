<?php

namespace App\Filament\Resources\Incidents\Widgets;

use App\Models\Incident;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;

class OpenIncidents extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Incident::query()
                    ->whereIn('estado', ['programado', 'en_proceso'])
                    ->whereDate('fecha_ocurrencia', now())
            )
            ->heading('Incidentes Para Hoy')
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
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('fecha_ocurrencia')
                    ->label('Fecha Ocurrencia')
                    ->date('d/m/Y'),
            ])
            ->actions([
                Action::make('iniciar_investigacion')
                    ->label('Iniciar Investigación')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('primary')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Iniciar Proceso de Investigación')
                    ->action(function (Incident $record) {
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
            ->emptyStateHeading('No hay incidentes programados para hoy')
            ->emptyStateDescription('Excelente gestión de seguridad.')
            ->paginated(false);
    }
}
