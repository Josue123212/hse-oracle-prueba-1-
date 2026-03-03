<?php

namespace App\Filament\Resources\Programs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Programa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('parent.nombre')
                    ->label('Pertenece a')
                    ->placeholder('Principal')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('anio')
                    ->label('Año')
                    ->sortable(),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'borrador' => 'gray',
                        'aprobado' => 'success',
                        'cerrado' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('supervisor.nombre')
                    ->label('Supervisor')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label('Filtrar por Programa Padre')
                    ->relationship('parent', 'nombre'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\Action::make('pdf')
                        ->label('PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn ($record) => route('programs.pdf', $record))
                        ->openUrlInNewTab(),
                    \Filament\Actions\Action::make('excel')
                        ->label('Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn ($record) => route('programs.excel', $record))
                        ->openUrlInNewTab(),
                ])
                ->label('Reportes')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
