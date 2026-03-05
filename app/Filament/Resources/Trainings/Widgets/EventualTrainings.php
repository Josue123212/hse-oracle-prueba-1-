<?php

namespace App\Filament\Resources\Trainings\Widgets;

use App\Filament\Widgets\BaseEventualActivityWidget;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;

class EventualTrainings extends BaseEventualActivityWidget
{
    protected function getActivityType(): string
    {
        return 'capacitacion';
    }

    protected function getHeadingTitle(): string
    {
        return 'Actividades Eventuales - Capacitaciones';
    }

    protected function getActivityLabel(): string
    {
        return 'Capacitación';
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with(['training.program', 'training.responsable']);
    }

    protected function getIniciaFormDetails(): array
    {
        return [
            \Filament\Schemas\Components\Grid::make(2)
                ->schema([
                    TextInput::make('data.asistentes_reales')
                        ->label('Asistentes Reales')
                        ->numeric(),
                    Textarea::make('data.comentarios')
                        ->label('Comentarios Adicionales'),
                ]),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('training.program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('training.tema')
                    ->label('Tema')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Split::make([
                    Tables\Columns\TextColumn::make('frecuencia')
                        ->badge()
                        ->color('warning'),
                ]),
            ])->space(3),
        ];
    }
}
