<?php

namespace App\Filament\Resources\Committees\Widgets;

use App\Models\ActivityExecution;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Widgets\BaseScheduledActivityWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;

class CommitteesForToday extends BaseScheduledActivityWidget
{
    protected function getActivityType(): string
    {
        return 'comite';
    }

    protected function getHeadingTitle(): string
    {
        return 'Comités Programados para Hoy';
    }

    protected function getActivityLabel(): string
    {
        return 'Comité';
    }

    protected function getObservacionLabel(): string
    {
        return 'Resumen de la Reunión';
    }

    protected function getEvidenciaLabel(): string
    {
        return 'Acta de Reunión (Archivo)';
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with(['activity.committee.program']);
    }

    protected function getTableColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('activity.committee.program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('activity.committee.nombre')
                    ->label('Reunión')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('activity.committee.tema_principal')
                    ->label('Tema')
                    ->limit(30)
                    ->color('gray'),

                Split::make([
                    Tables\Columns\TextColumn::make('estado')
                        ->label('Estado')
                        ->badge(),
                ]),
            ])->space(3),
        ];
    }

    protected function getIniciaFormDetails(): array
    {
        return [
            \Filament\Schemas\Components\Grid::make(2)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('data.asistentes')
                        ->label('Número de Asistentes')
                        ->numeric(),
                    \Filament\Forms\Components\Textarea::make('data.compromisos')
                        ->label('Compromisos Adquiridos')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
