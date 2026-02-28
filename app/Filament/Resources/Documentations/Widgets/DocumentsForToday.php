<?php

namespace App\Filament\Resources\Documentations\Widgets;

use App\Models\Documentation;
use App\Models\Activity;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use App\Enums\ActivityState;

class DocumentsForToday extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '15s';

    #[On('activity-updated')]
    public function refresh(): void
    {
        // Livewire automatically re-renders the component
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->whereIn('estado', [
                        ActivityState::PROGRAMADO->value,
                        ActivityState::EN_PROCESO->value,
                        ActivityState::EJECUTADO->value
                    ])
                    ->where(function ($query) {
                        $query->where('tipo', 'documentacion')
                              ->orWhereHas('documentation');
                    })
                    ->whereHas('executions', function ($query) {
                        $query->whereDate('fecha_programada', now());
                    })
            )
            ->heading('Documentación Programada para Hoy')
            ->columns([
                Tables\Columns\TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Actividad / Documento')
                    ->weight('bold')
                    ->searchable()
                    ->description(fn (Activity $record) => $record->documentation ? "Documento creado: {$record->documentation->version}" : "Pendiente de creación"),
                Tables\Columns\TextColumn::make('frecuencia')
                    ->label('Frecuencia')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'eventual' => 'warning',
                        default => 'info',
                    })
                    ->formatStateUsing(function ($state) {
                        return [
                            'diario' => 'Diario',
                            'semanal' => 'Semanal',
                            'mensual' => 'Mensual',
                            'trimestral' => 'Trimestral',
                            'semestral' => 'Semestral',
                            'anual' => 'Anual',
                            'eventual' => 'Eventual',
                            'unico' => 'Único',
                        ][$state] ?? ucfirst($state);
                    }),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha Programada')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(fn (Activity $record): string => "{$record->ejecuciones_realizadas} / {$record->veces_al_anio}")
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado Actividad')
                    ->badge(),
                Tables\Columns\TextColumn::make('documentation.estado')
                    ->label('Estado Doc.')
                    ->badge()
                    ->state(fn (Activity $record) => $record->documentation ? $record->documentation->estado : 'pendiente')
                    ->colors([
                        'warning' => 'borrador',
                        'info' => 'revision',
                        'success' => 'aprobado',
                        'danger' => 'obsoleto',
                        'gray' => 'pendiente',
                    ]),
            ])
            ->actions([
                Action::make('iniciar_documento')
                    ->label(fn (Activity $record) => $record->documentation ? 'Editar Documento' : 'Iniciar Documento')
                    ->icon(fn (Activity $record) => $record->documentation ? 'heroicon-o-pencil-square' : 'heroicon-o-plus')
                    ->color('primary')
                    ->button()
                    ->action(function (Activity $record) {
                        $doc = $record->documentation;
                        if (!$doc) {
                            $doc = Documentation::create([
                                'program_id' => $record->program_id,
                                'activity_id' => $record->id,
                                'titulo' => $record->nombre,
                                'tipo_documento' => 'otro',
                                'version' => '1.0',
                                'estado' => 'borrador',
                                'responsable_id' => auth()->id() ?? $record->responsable_id,
                            ]);
                            Notification::make()
                                ->title('Documento iniciado')
                                ->success()
                                ->send();
                        }
                        return redirect()->to(\App\Filament\Resources\Documentations\DocumentationResource::getUrl('edit', ['record' => $doc]));
                    }),

                Action::make('aprobar_documento')
                    ->label(function (Activity $record) {
                        $progress = "{$record->ejecuciones_realizadas}/{$record->veces_al_anio}";
                        return "Aprobar / Publicar ($progress)";
                    })
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->visible(fn (Activity $record) => $record->documentation && !in_array($record->documentation->estado, ['aprobado', 'obsoleto']))
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Documento')
                    ->modalDescription(function (Activity $record) {
                        $progress = "Progreso actual: {$record->ejecuciones_realizadas}/{$record->veces_al_anio}.";
                        return "El documento pasará a estado 'Aprobado'. $progress";
                    })
                    ->action(function (Activity $record) {
                        $doc = $record->documentation;
                        if ($doc) {
                            $record->increment('ejecuciones_realizadas');
                            $record->refresh(); // Get updated value
                            
                            $record->updateStatusBasedOnProgress();
                            
                            $doc->update(['estado' => 'aprobado']);
                            
                            $message = "Aprobación registrada. Progreso: {$record->ejecuciones_realizadas}/{$record->veces_al_anio}. Estado: " . ucfirst($record->estado);
                            
                            Notification::make()
                                ->title($message)
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->emptyStateHeading('No hay documentación programada para hoy')
            ->paginated(false);
    }
}
