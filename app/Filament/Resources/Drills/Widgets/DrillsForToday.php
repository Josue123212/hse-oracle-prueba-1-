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
                    ->where(function ($query) {
                        $today = now();
                        $query->where(function ($q) use ($today) {
                                $q->whereIn('frecuencia', ['unico', 'eventual'])
                                ->whereDate('fecha_programada', $today);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'diario')
                                ->whereDate('fecha_programada', '<=', $today);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'semanal')
                                ->whereDate('fecha_programada', '<=', $today)
                                ->whereRaw("EXTRACT(DOW FROM fecha_programada) = ?", [$today->dayOfWeek]);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'mensual')
                                ->whereDate('fecha_programada', '<=', $today)
                                ->whereRaw("EXTRACT(DAY FROM fecha_programada) = ?", [$today->day]);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'trimestral')
                                ->whereDate('fecha_programada', '<=', $today)
                                ->whereRaw("EXTRACT(DAY FROM fecha_programada) = ?", [$today->day])
                                ->whereRaw("MOD((EXTRACT(YEAR FROM AGE(?, fecha_programada)) * 12 + EXTRACT(MONTH FROM AGE(?, fecha_programada))), 3) = 0", [$today, $today]);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'semestral')
                                ->whereDate('fecha_programada', '<=', $today)
                                ->whereRaw("EXTRACT(DAY FROM fecha_programada) = ?", [$today->day])
                                ->whereRaw("MOD((EXTRACT(YEAR FROM AGE(?, fecha_programada)) * 12 + EXTRACT(MONTH FROM AGE(?, fecha_programada))), 6) = 0", [$today, $today]);
                        })
                        ->orWhere(function ($q) use ($today) {
                                $q->where('frecuencia', 'anual')
                                ->whereDate('fecha_programada', '<=', $today)
                                ->whereRaw("EXTRACT(DAY FROM fecha_programada) = ?", [$today->day])
                                ->whereRaw("EXTRACT(MONTH FROM fecha_programada) = ?", [$today->month]);
                        });
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
