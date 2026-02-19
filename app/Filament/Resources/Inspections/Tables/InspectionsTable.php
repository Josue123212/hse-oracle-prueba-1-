<?php

namespace App\Filament\Resources\Inspections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class InspectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('inspectionType.nombre')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('activity.nombre')
                    ->label('Actividad')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('location.nombre')
                    ->label('Sede')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('fecha_programada')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'conforme' => 'success',
                        'no_conforme' => 'danger',
                        'vencido' => 'warning',
                    }),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Inspector')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('archivo')
                    ->label('Documento')
                    ->getStateUsing(fn ($record) => $record->archivo_detectado ? 'Ver' : '')
                    ->url(fn ($record) => $record->archivo_detectado ? Storage::url("inspecciones/{$record->anio}/" . str_pad((string) $record->mes, 2, '0', STR_PAD_LEFT) . "/inspeccion-{$record->id}.pdf") : null)
                    ->openUrlInNewTab(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                \Filament\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route('inspections.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
