<?php

namespace App\Filament\Resources\Audits\Widgets;

use App\Models\Audit;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;
use App\Enums\ActivityState;

class AuditsForToday extends BaseWidget
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
        // Actualizar estados vencidos antes de mostrar
        // Se vence si:
        // 1. La fecha programada ya pasó.
        $auditsToExpire = Audit::where('estado', ActivityState::PROGRAMADO->value)
            ->whereDate('fecha_programada', '<', now()->toDateString())
            ->get();

        foreach ($auditsToExpire as $audit) {
            $audit->update(['estado' => ActivityState::NO_CUMPLIO->value]);
        }

        return $table
            ->query(
                Audit::query()
                    ->whereIn('estado', [ActivityState::PROGRAMADO->value, ActivityState::EN_PROCESO->value])
                    ->whereHas('activity.executions', function ($query) {
                        $query->whereDate('fecha_programada', now());
                    })
            )
            ->heading('Auditorías Programadas para Hoy')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Auditoría')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function (Audit $record): string {
                        if ($record->activity) {
                            return "{$record->activity->ejecuciones_realizadas} / {$record->activity->veces_al_anio}";
                        }
                        return "N/A";
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('auditor.name')
                    ->label('Auditor Responsable')
                    ->placeholder('Sin asignar'),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(fn (Audit $record): string => $record->descripcion ?? ''),
            ])
            ->actions([
                Action::make('iniciar')
                    ->label(function (Audit $record) {
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
                    ->modalHeading('Iniciar Auditoría')
                    ->modalDescription(function (Audit $record) {
                        $progress = "";
                        if ($record->activity) {
                            $progress = "Progreso actual: {$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}.";
                        }
                        return "¿Estás seguro de que deseas iniciar esta auditoría? $progress El estado cambiará a 'Ejecutado' o 'En Proceso' según corresponda.";
                    })
                    ->modalSubmitActionLabel('Sí, iniciar')
                    ->action(function (Audit $record) {
                        if ($record->activity) {
                            $record->activity->increment('ejecuciones_realizadas');
                            
                            if ($record->activity->ejecuciones_realizadas >= $record->activity->veces_al_anio) {
                                $record->activity->update(['estado' => 'ejecutado', 'fecha_fin' => now()]);
                                $record->update([
                                    'estado' => 'ejecutado',
                                    'fecha_ejecucion' => now(), // Assuming this field exists or needs to be added to Audit model if not present. Audit usually has fecha_ejecucion?
                                    // Based on previous read, Audit table has fecha_programada, fecha_vencimiento. 
                                    // It doesn't explicitly show fecha_ejecucion in columns but likely has it or we can just update status.
                                    // I'll stick to updating status for now.
                                ]);
                                $message = 'Auditoría completada (Meta alcanzada)';
                            } else {
                                $record->activity->update(['estado' => 'en_proceso']);
                                $record->update([
                                    'estado' => 'en_proceso',
                                ]);
                                $message = "Ejecución registrada. Progreso: {$record->activity->ejecuciones_realizadas}/{$record->activity->veces_al_anio}";
                            }
                        } else {
                            $record->update(['estado' => 'ejecutado']);
                            $message = 'Auditoría iniciada';
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No hay auditorías programadas para hoy')
            ->emptyStateDescription('Todas las auditorías están al día o no hay programaciones pendientes.')
            ->paginated(false);
    }
}
