<?php

namespace App\Filament\Resources\Drills\Widgets;

use App\Models\Drill;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Livewire\Attributes\On;

class DrillsForToday extends BaseWidget
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
                Drill::query()
                    ->whereIn('estado', ['programado', 'en_proceso'])
                    ->whereHas('activity.executions', function ($query) {
                        $query->whereDate('fecha_programada', now());
                    })
            )
            ->heading('Simulacros Programados para Hoy')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Simulacro')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('escenario')
                    ->label('Escenario')
                    ->limit(30),
                Tables\Columns\TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function (Drill $record): string {
                        return "{$record->ejecuciones_realizadas} / {$record->veces_al_anio}";
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Fecha')
                    ->formatStateUsing(fn () => now()->format('d/m/Y')),
            ])
            ->actions([
                Action::make('iniciar_simulacro')
                    ->label(function (Drill $record) {
                        return "Ejecutar Simulacro ({$record->ejecuciones_realizadas}/{$record->veces_al_anio})";
                    })
                    ->icon('heroicon-o-megaphone')
                    ->color('danger')
                    ->button()
                    ->form([
                        TextInput::make('participantes_count')
                            ->label('Número de Participantes Reales')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function (Drill $record, array $data) {
                        $record->increment('ejecuciones_realizadas');
                        
                        if ($record->ejecuciones_realizadas >= $record->veces_al_anio) {
                            $record->update([
                                'estado' => 'ejecutado',
                                'fecha_ejecucion' => now(),
                                'participantes_count' => $data['participantes_count'],
                            ]);
                            if ($record->activity) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                            }
                            $message = 'Simulacro completado (Meta alcanzada)';
                        } else {
                            $record->update([
                                'estado' => 'en_proceso',
                                'participantes_count' => $data['participantes_count'],
                            ]);
                            if ($record->activity) {
                                $record->activity->update(['estado' => 'en_proceso']);
                            }
                            $message = "Ejecución registrada. Progreso: {$record->ejecuciones_realizadas}/{$record->veces_al_anio}";
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No hay simulacros programados para hoy')
            ->paginated(false);
    }
}
