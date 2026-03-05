<?php

namespace App\Filament\Resources\Promotions\Widgets;

use App\Models\ActivityExecution;
use Filament\Tables;
use App\Filament\Widgets\BaseScheduledActivityWidget;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;

class PromotionsForToday extends BaseScheduledActivityWidget
{
    protected function getActivityType(): string
    {
        return 'promocion';
    }

    protected function getActivityLabel(): string
    {
        return 'Promoción';
    }

    protected function getHeadingTitle(): string
    {
        return 'Campañas de Promoción para Hoy';
    }

    protected function getObservacionLabel(): string
    {
        return 'Informe de la Campaña';
    }

    protected function getEvidenciaLabel(): string
    {
        return 'Evidencia (Fotos/Material)';
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with(['activity.promotion.program'])
            ->whereHas('activity', fn ($query) => $query->where('tipo', 'promocion'));
    }

    protected function getTableColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('activity.promotion.program.nombre')
                    ->label('Programa')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('activity.nombre')
                    ->label('Actividad')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('activity.promotion.publico_objetivo')
                    ->label('Público Objetivo')
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
            \Filament\Schemas\Components\Section::make('Detalles de la Promoción')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('data.alcance_real')
                                ->label('Personas Alcanzadas')
                                ->numeric(),
                            \Filament\Forms\Components\TextInput::make('data.canales_utilizados')
                                ->label('Canales Utilizados')
                                ->placeholder('Ej: Email, Intranet, Cartelera'),
                        ]),
                ]),
        ];
    }
}
