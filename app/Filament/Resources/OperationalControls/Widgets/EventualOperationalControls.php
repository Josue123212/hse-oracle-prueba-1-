<?php

namespace App\Filament\Resources\OperationalControls\Widgets;

use App\Models\Activity;
use App\Models\ActivityExecution;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Widgets\BaseEventualActivityWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class EventualOperationalControls extends BaseEventualActivityWidget
{
    protected function getActivityType(): string
    {
        return 'control_operacional';
    }

    protected function getActivityLabel(): string
    {
        return 'Control Operacional';
    }

    protected function getHeadingTitle(): string
    {
        return 'Actividades Eventuales - Controles Operacionales';
    }

    protected function getObservacionLabel(): string
    {
        return 'Observaciones';
    }

    protected function getEvidenciaLabel(): string
    {
        return 'Evidencia (Archivo)';
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(function ($query) {
             $query->where('tipo', 'control_operacional')
                   ->orWhereHas('operationalControl');
        });
    }

    protected function getTableColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Actividad / Proceso')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('descripcion')
                        ->label('Descripción')
                        ->limit(50)
                        ->color('gray')
                        ->description(fn (Activity $record) => $record->operationalControl ? "Parámetro: {$record->operationalControl->parametro}" : null),

                Split::make([
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
                    \Filament\Forms\Components\TextInput::make('data.valor_observado')
                        ->label('Valor Observado'),
                    \Filament\Forms\Components\Select::make('data.cumplimiento')
                        ->label('¿Cumple?')
                        ->options([
                            'si' => 'Sí',
                            'no' => 'No',
                        ]),
                ]),
        ];
    }
}
