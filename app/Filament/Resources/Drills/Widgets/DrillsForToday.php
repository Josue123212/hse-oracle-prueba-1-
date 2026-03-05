<?php

namespace App\Filament\Resources\Drills\Widgets;

use App\Filament\Widgets\BaseScheduledActivityWidget;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid; // Kept for consistency with observed code, though likely should be Forms\Components\Grid
use Filament\Forms\Components\Section;

class DrillsForToday extends BaseScheduledActivityWidget
{
    protected function getActivityType(): string
    {
        return 'simulacro';
    }

    protected function getHeadingTitle(): string
    {
        return 'Simulacros Programados para Hoy';
    }

    protected function getActivityLabel(): string
    {
        return 'Simulacro';
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with(['activity.drill.program']);
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
                Tables\Columns\TextColumn::make('activity.drill.program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('danger'),
                
                Tables\Columns\TextColumn::make('activity.drill.nombre')
                    ->label('Simulacro')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('activity.drill.escenario')
                    ->label('Escenario')
                    ->limit(30)
                    ->color('gray'),

                Split::make([
                    Tables\Columns\TextColumn::make('fecha_programada')
                        ->date('H:i')
                        ->icon('heroicon-o-clock')
                        ->color('gray'),
                    
                    Tables\Columns\TextColumn::make('estado')
                        ->badge(),
                ]),
            ])->space(3),
        ];
    }
}
