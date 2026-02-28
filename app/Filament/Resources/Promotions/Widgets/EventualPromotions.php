<?php

namespace App\Filament\Resources\Promotions\Widgets;

use App\Models\Promotion;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class EventualPromotions extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Promotion::query()
            ->whereIn('estado', ['planificado', 'en_curso'])
            ->whereHas('activity', function ($q) {
                $q->where('frecuencia', 'eventual');
            })->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Promotion::query()
                    ->whereIn('estado', ['planificado', 'en_curso'])
                    ->whereHas('activity', function ($q) {
                        $q->where('frecuencia', 'eventual');
                    })
            )
            ->heading('Promociones Eventuales / No Programadas')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('activity.nombre')
                    ->label('Actividad')
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
                    ->modalHeading('Iniciar Promoción')
                    ->modalDescription('¿Estás seguro de que deseas iniciar esta promoción? El estado cambiará a "Finalizado" o "En Curso" según corresponda.')
                    ->modalSubmitActionLabel('Sí, iniciar')
                    ->action(function ($record) {
                        if ($record->activity) {
                            $record->activity->increment('ejecuciones_realizadas');
                            
                            if ($record->activity->ejecuciones_realizadas >= $record->activity->veces_al_anio) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                                $record->update([
                                    'estado' => 'finalizado',
                                ]);
                                $message = 'Promoción completada (Meta alcanzada)';
                            } else {
                                $record->activity->update(['estado' => 'en_proceso']);
                                $record->update([
                                    'estado' => 'en_curso',
                                ]);
                                $message = "Ejecución registrada.";
                            }
                        } else {
                            $record->update([
                                'estado' => 'en_curso',
                            ]);
                            $message = 'Promoción iniciada';
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
