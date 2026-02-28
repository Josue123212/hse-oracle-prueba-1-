<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Actividad')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'primary' => 'general',
                        'warning' => 'auditoria',
                        'success' => 'inspeccion',
                        'info' => 'capacitacion',
                        'danger' => ['simulacro', 'incidente'],
                        'gray' => 'documentacion',
                        'success' => ['promocion', 'inspeccion'],
                        'warning' => ['control_operacional', 'auditoria'],
                        'info' => ['comite', 'capacitacion'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'auditoria' => 'Auditoría',
                        'inspeccion' => 'Inspección',
                        'capacitacion' => 'Capacitación',
                        'simulacro' => 'Simulacro',
                        'incidente' => 'Incidente',
                        'comite' => 'Comité',
                        'documentacion' => 'Documentación',
                        'promocion' => 'Promoción',
                        'control_operacional' => 'Control Operacional',
                        default => ucfirst($state),
                    }),

                TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->searchable()
                    ->badge(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => \App\Enums\ActivityState::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn ($state) => \App\Enums\ActivityState::tryFrom($state)?->getColor() ?? 'gray'),

                TextColumn::make('location.nombre')
                    ->label('Sede')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('scheduled_months')
                    ->label('Meses Programados')
                    ->badge()
                    ->color('info')
                    ->separator(','),

                TextColumn::make('fecha_proxima')
                    ->label('Fecha Ejecución')
                    ->date('d/m/Y'),

                TextColumn::make('veces_al_anio')
                    ->label('Veces al Año')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function ($record): string {
                        return "{$record->ejecuciones_realizadas} / {$record->veces_al_anio}";
                    })
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('responsable.name')
                    ->label('Responsable'),

                TextColumn::make('percentage_complete')
                    ->label('Progreso (%)')
                    ->state(function ($record): string {
                        $target = (int) $record->veces_al_anio;
                        $completed = (int) $record->ejecuciones_realizadas;
                        
                        if ($target <= 0) {
                            return '0';
                        }
                        
                        $percentage = ($completed / $target) * 100;
                        return number_format($percentage, 0);
                    })
                    ->suffix('%')
                    ->alignCenter(),

                IconColumn::make('es_obligatoria')
                    ->label('Obligatoria')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseColor('gray'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
