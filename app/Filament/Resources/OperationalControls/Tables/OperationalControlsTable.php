<?php

namespace App\Filament\Resources\OperationalControls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Enums\ActivityState;

class OperationalControlsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('activity.fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('activity.scheduled_months')
                    ->label('Meses Programados')
                    ->badge()
                    ->color('info')
                    ->separator(','),

                TextColumn::make('activity.nombre')
                    ->label('Actividad')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('nombre_proceso')
                    ->label('Proceso')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parametro')
                    ->label('Parámetro')
                    ->searchable(),

                TextColumn::make('valor_esperado')
                    ->label('Valor Esperado'),

                TextColumn::make('activity.fecha_proxima')
                    ->label('Fecha Ejecución')
                    ->date('d/m/Y'),

                TextColumn::make('estado')
                    ->badge()
                    ->sortable(),
                
                TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function ($record): string {
                        return "{$record->ejecuciones_realizadas} / {$record->veces_al_anio}";
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
