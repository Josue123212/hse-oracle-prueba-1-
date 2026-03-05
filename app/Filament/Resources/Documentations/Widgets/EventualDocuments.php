<?php

namespace App\Filament\Resources\Documentations\Widgets;

use App\Models\Activity;
use Filament\Tables;
use App\Filament\Widgets\BaseEventualActivityWidget;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;

class EventualDocuments extends BaseEventualActivityWidget
{
    protected function getActivityType(): string
    {
        return 'documentacion';
    }

    protected function getActivityLabel(): string
    {
        return 'Documento';
    }

    protected function getHeadingTitle(): string
    {
        return 'Actividades Eventuales - Documentación';
    }

    protected function getObservacionLabel(): string
    {
        return 'Observaciones / Cambios';
    }

    protected function getEvidenciaLabel(): string
    {
        return 'Archivo del Documento';
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with(['documentation'])
            ->where(function ($query) {
                $query->where('tipo', 'documentacion')
                      ->orWhereHas('documentation');
            });
    }

    protected function getTableColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Documento')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->color('gray'),

                Split::make([
                    Tables\Columns\TextColumn::make('documentation.version')
                        ->default('v1.0')
                        ->formatStateUsing(fn ($state) => "v{$state}")
                        ->badge()
                        ->color('info'),

                    Tables\Columns\TextColumn::make('frecuencia')
                        ->badge()
                        ->color('warning'),
                ]),
            ])->space(3),
        ];
    }

    protected function getIniciaFormDetails(): array
    {
        return [
            \Filament\Schemas\Components\Grid::make(2)
                ->schema([
                    \Filament\Forms\Components\Textarea::make('data.notas')
                        ->label('Versión Actualizada'),
                    \Filament\Forms\Components\DatePicker::make('data.fecha_aprobacion')
                        ->label('Fecha Aprobación')
                        ->default(now()),
                ]),
        ];
    }
}
