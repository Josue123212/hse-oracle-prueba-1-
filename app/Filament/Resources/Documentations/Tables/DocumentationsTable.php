<?php

namespace App\Filament\Resources\Documentations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Documentation;
use App\Models\Activity;
use App\Enums\ActivityState;
use Livewire\Livewire;

class DocumentationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('activity.fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('activity.scheduled_months')
                    ->label('Meses Programados')
                    ->badge()
                    ->color('info')
                    ->separator(','),

                TextColumn::make('activity.fecha_proxima')
                    ->label('Fecha Ejecución')
                    ->date('d/m/Y'),

                TextColumn::make('activity.titulo')
                    ->label('Actividad')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('tipo_documento')
                    ->badge()
                    ->label('Tipo'),
                
                TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function ($record): string {
                        if ($record->activity) {
                            return "{$record->activity->ejecuciones_realizadas} / {$record->activity->veces_al_anio}";
                        }
                        return "N/A";
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('responsable.name')
                    ->label('Responsable')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->after(function (Documentation $record) {
                        // Force update activity if exists to ensure sync
                        if ($record->activity_id) {
                            $activity = Activity::find($record->activity_id);
                            if ($activity) {
                                $activity->update([
                                    'fecha_inicio' => $record->fecha_programada,
                                    'fecha_fin' => $record->fecha_aprobacion ?? $record->fecha_programada,
                                    'nombre' => $record->titulo,
                                ]);
                            }
                        }
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
