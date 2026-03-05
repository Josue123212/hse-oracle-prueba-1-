<?php

namespace App\Filament\Resources\Trainings\Widgets;

use App\Filament\Widgets\BaseScheduledActivityWidget;
use App\Models\ActivityExecution;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;

class TrainingsForToday extends BaseScheduledActivityWidget
{
    protected function getActivityType(): string
    {
        return 'capacitacion';
    }

    protected function getHeadingTitle(): string
    {
        return 'Capacitaciones Programadas para Hoy';
    }

    protected function getActivityLabel(): string
    {
        return 'Capacitación';
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with(['activity.component.program', 'activity.training']);
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
                Tables\Columns\TextColumn::make('activity.component.program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('activity.nombre')
                    ->label('Tema / Actividad')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('activity.frecuencia')
                    ->label('Frecuencia')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Split::make([
                    Tables\Columns\TextColumn::make('activity.training.hora_inicio')
                        ->label('Hora')
                        ->time('H:i')
                        ->default(fn (ActivityExecution $record) => $record->activity->training?->hora_inicio)
                        ->icon('heroicon-o-clock')
                        ->color('gray'),
                    
                    Tables\Columns\TextColumn::make('estado')
                        ->label('Estado')
                        ->badge(),
                ]),
            ])->space(3),
        ];
    }
}
