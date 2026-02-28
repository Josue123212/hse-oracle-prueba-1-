<?php

namespace App\Filament\Resources\Committees\Widgets;

use App\Models\Committee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Livewire\Attributes\On;

class CommitteesForToday extends BaseWidget
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
                Committee::query()
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
                             $q->whereNull('frecuencia') // Fallback for old records without frequency
                             ->whereDate('fecha_programada', '<=', $today);
                        });
                    })
            )
            ->heading('Reuniones de Comité Programadas para Hoy')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Reunión')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tema_principal')
                    ->label('Tema')
                    ->limit(30),
                Tables\Columns\TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function (Committee $record): string {
                        return "{$record->ejecuciones_realizadas} / {$record->veces_al_anio}";
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Fecha')
                    ->date('d/m/Y'),
            ])
            ->actions([
                Action::make('iniciar_sesion')
                    ->label(function (Committee $record) {
                        return "Iniciar Sesión ({$record->ejecuciones_realizadas}/{$record->veces_al_anio})";
                    })
                    ->icon('heroicon-o-users')
                    ->color('success')
                    ->button()
                    ->form([
                        Textarea::make('acuerdos')
                            ->label('Acuerdos Iniciales / Notas')
                            ->rows(3)
                            ->placeholder('Registre los puntos clave discutidos...'),
                    ])
                    ->action(function (Committee $record, array $data) {
                        $record->increment('ejecuciones_realizadas');
                        
                        if ($record->ejecuciones_realizadas >= $record->veces_al_anio) {
                            $record->update([
                                'estado' => 'realizado',
                                'fecha_realizada' => now(), // We can keep this internally or remove it from fillable? It exists in DB.
                                'acuerdos' => $data['acuerdos'],
                            ]);
                            if ($record->activity) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                            }
                            $message = 'Sesión completada (Meta alcanzada)';
                        } else {
                            $record->update([
                                'estado' => 'en_proceso',
                                'acuerdos' => $data['acuerdos'],
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
            ->emptyStateHeading('No hay reuniones de comité para hoy')
            ->paginated(false);
    }
}
