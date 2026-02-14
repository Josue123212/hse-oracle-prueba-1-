<?php

namespace App\Filament\Resources\TrainingMaterials\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class TrainingMaterialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('training.titulo')
                    ->label('Capacitación')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre del material')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url_material')
                    ->label('URL del material')
                    ->searchable(),
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
