<?php

namespace App\Filament\Resources\Promotions\Widgets;

use App\Models\Activity;
use Filament\Tables;
use App\Filament\Widgets\BaseEventualActivityWidget;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;

class EventualPromotions extends BaseEventualActivityWidget
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
        return 'Actividades Eventuales - Promociones';
    }

    protected function getObservacionLabel(): string
    {
        return 'Informe de la Campaña';
    }

    protected function getEvidenciaLabel(): string
    {
        return 'Evidencia (Archivo)';
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(function ($query) {
                $query->where('tipo', 'promocion')
                      ->orWhereHas('promotion');
            });
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
