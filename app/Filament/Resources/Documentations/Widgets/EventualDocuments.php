<?php

namespace App\Filament\Resources\Documentations\Widgets;

use App\Models\Documentation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class EventualDocuments extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2; // Show after PendingDocuments (or before? let's make it 2 for now, and Pending 1)

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Documentation::query()
                    ->whereIn('estado', ['borrador', 'revision'])
                    ->whereHas('activity', function ($q) {
                        $q->where('frecuencia', 'eventual');
                    })
            )
            ->heading('Documentación Eventual / No Programada')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('version')
                    ->label('Ver.'),
                Tables\Columns\TextColumn::make('activity.frecuencia')
                    ->label('Frecuencia')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha')
                    ->date('d/m/Y'), // Use actual date from record if available
                Tables\Columns\TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function (Documentation $record): string {
                        if ($record->activity) {
                            return "{$record->activity->ejecuciones_realizadas} / {$record->activity->veces_al_anio}";
                        }
                        return "N/A";
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'borrador',
                        'info' => 'revision',
                    ]),
            ])
            ->actions([
                Action::make('aprobar_documento')
                    ->label(function (Documentation $record) {
                        $progress = "N/A";
                        if ($record->activity) {
                            $progress = "{$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}";
                        }
                        return "Aprobar / Publicar ($progress)";
                    })
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Documento')
                    ->modalDescription(function (Documentation $record) {
                        $progress = "";
                        if ($record->activity) {
                            $progress = "Progreso actual: {$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}.";
                        }
                        return "El documento pasará a estado 'Aprobado'. $progress";
                    })
                    ->action(function (Documentation $record) {
                        if ($record->activity) {
                            $record->activity->increment('ejecuciones_realizadas');
                            
                            if ($record->activity->ejecuciones_realizadas >= $record->activity->veces_al_anio) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                                $record->update([
                                    'estado' => 'aprobado',
                                ]);
                                $message = 'Documento aprobado y actividad completada (Meta alcanzada)';
                            } else {
                                $record->activity->update(['estado' => 'en_proceso']);
                                $record->update([
                                    'estado' => 'aprobado',
                                ]);
                                $message = "Aprobación registrada. Progreso: {$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}";
                            }
                        } else {
                            $record->update([
                                'estado' => 'aprobado',
                            ]);
                            $message = 'Documento aprobado exitosamente';
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No hay documentación eventual pendiente')
            ->paginated(false);
    }
}
