<?php

namespace App\Filament\Resources\TrainingAttendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Filament\Schemas\Components\Section;

class TrainingAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('training.titulo')
                    ->label('Capacitación')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Participante')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pendiente',
                        'info' => 'en_progreso',
                        'success' => 'completado',
                        'danger' => 'reprobado',
                    ])
                    ->sortable(),
                TextColumn::make('fecha_inicio')
                    ->label('Fecha inicio')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('fecha_fin')
                    ->label('Fecha fin')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
