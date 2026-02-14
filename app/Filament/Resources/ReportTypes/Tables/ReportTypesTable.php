<?php

namespace App\Filament\Resources\ReportTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;

class ReportTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('frecuencia_requerida')
                    ->label('Frecuencia requerida')
                    ->formatStateUsing(fn (string $state) => [
                        'diario' => 'Diario',
                        'semanal' => 'Semanal',
                        'mensual' => 'Mensual',
                    ][$state] ?? $state)
                    ->searchable()
                    ->sortable(),
                BooleanColumn::make('es_obligatorio')
                    ->label('¿Es obligatorio?')
                    ->searchable()
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
