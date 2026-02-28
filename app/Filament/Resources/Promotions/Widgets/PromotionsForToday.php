<?php

namespace App\Filament\Resources\Promotions\Widgets;

use App\Models\Promotion;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

class PromotionsForToday extends BaseWidget
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
                Promotion::query()
                    ->whereIn('estado', ['planificado', 'en_curso'])
                    ->whereHas('activity.executions', function ($query) {
                        $query->whereDate('fecha_programada', now());
                    })
            )
            ->heading('Campañas de Promoción para Hoy')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('activity.nombre')
                    ->label('Actividad')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activity.frecuencia')
                    ->label('Frecuencia')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('publico_objetivo')
                    ->label('Público'),
                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Programada')
                    ->date('d/m/Y'),
            ])
            ->actions([
                Action::make('lanzar_campana')
                    ->label('Lanzar Campaña')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('primary')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Lanzar Campaña')
                    ->modalDescription('La campaña pasará a estado "En Curso" y se registrará una ejecución.')
                    ->modalSubmitActionLabel('Lanzar ahora')
                    ->action(function (Promotion $record) {
                        if ($record->activity) {
                            $record->activity->increment('ejecuciones_realizadas');
                            
                            if ($record->activity->ejecuciones_realizadas >= $record->activity->veces_al_anio) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                                $record->update([
                                    'estado' => 'finalizado',
                                ]);
                                $message = 'Campaña completada (Meta alcanzada)';
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
                            $message = 'Campaña lanzada';
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No hay campañas planificadas para hoy')
            ->paginated(false);
    }
}
