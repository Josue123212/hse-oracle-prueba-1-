<?php

namespace App\Filament\Resources\Drills\Widgets;

use App\Filament\Widgets\BaseEventualActivityWidget;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;

class EventualDrills extends BaseEventualActivityWidget
{
    protected function getActivityType(): string
    {
        return 'simulacro';
    }

    protected function getHeadingTitle(): string
    {
        return 'Actividades Eventuales - Simulacros';
    }

    protected function getActivityLabel(): string
    {
        return 'Simulacro';
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with(['drill.program']);
    }

    protected function getIniciaFormDetails(): array
    {
        return [
            \Filament\Schemas\Components\Grid::make(2)
                ->schema([
                    TextInput::make('data.participantes')
                        ->label('Participantes')
                        ->numeric(),
                    TextInput::make('data.duracion_real')
                        ->label('Duración Real (minutos)')
                        ->numeric(),
                    Textarea::make('data.conclusiones')
                        ->label('Conclusiones')
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('drill.program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('drill.nombre')
                    ->label('Simulacro')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('drill.escenario')
                    ->label('Escenario')
                    ->limit(30)
                    ->color('gray'),

                Split::make([
                    Tables\Columns\TextColumn::make('frecuencia')
                        ->badge()
                        ->color('warning'),
                ]),
            ])->space(3),
        ];
    }
}
