<?php

namespace App\Filament\Resources\Repositories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;

class RepositoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('url')
                    ->label('Ruta/URL')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('repositoriable_type')
                    ->label('Tipo')
                    ->toggleable(),
                TextColumn::make('repositoriable_id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Responsable')
                    ->searchable(),
                TextColumn::make('ultima_verificacion')
                    ->label('Última verificación')
                    ->dateTime()
                    ->toggleable(),
                TextColumn::make('archivos')
                    ->label('Total archivos')
                    ->getStateUsing(fn ($record) => count(Storage::disk('public')->files($record->url ?? ''))),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('ver_archivos')
                    ->label('Ver archivos')
                    ->icon('heroicon-o-folder-open')
                    ->modalHeading('Archivos del repositorio')
                    ->modalSubmitAction(false)
                    ->modalContent(fn ($record) => view('filament.repositories.files-list', [
                        'items' => array_map(
                            fn ($path) => [
                                'name' => basename($path),
                                'url' => Storage::disk('public')->url($path),
                            ],
                            Storage::disk('public')->files($record->url ?? '')
                        ),
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
