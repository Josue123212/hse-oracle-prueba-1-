<?php

namespace App\Filament\Resources\OperationalControls\Widgets;

use App\Models\ActivityExecution;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Widgets\BaseScheduledActivityWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class OperationalControlsForToday extends BaseScheduledActivityWidget
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
        return 'Controles Operacionales Pendientes para Hoy';
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
        return $query->with(['activity.operationalControl', 'activity.program'])
            ->whereHas('activity', function ($query) {
                $query->where('tipo', 'control_operacional')
                      ->orWhereHas('operationalControl');
            });
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
                    ->label('Proceso / Actividad')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable()
                    ->description(fn (ActivityExecution $record) => $record->activity->operationalControl ? "Parámetro: {$record->activity->operationalControl->parametro}" : "Sin parámetro"),
                
                Tables\Columns\TextColumn::make('activity.operationalControl.valor_esperado')
                    ->label('Valor Esperado')
                    ->badge()
                    ->color('info'),

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
                    \Filament\Forms\Components\Select::make('data.cumple')
                        ->label('¿Cumple con el parámetro?')
                        ->options([
                            'si' => 'Sí',
                            'no' => 'No',
                        ])
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('data.valor_registrado')
                        ->label('Valor Registrado')
                        ->numeric(),
                ]),
        ];
    }
}
