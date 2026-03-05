<?php

namespace App\Filament\Resources\Documentations\Widgets;

use App\Models\ActivityExecution;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Widgets\BaseScheduledActivityWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;

use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;

class DocumentsForToday extends BaseScheduledActivityWidget
{
    protected function getActivityType(): string
    {
        return 'documentacion';
    }

    protected function getHeadingTitle(): string
    {
        return 'Documentación Programada para Hoy';
    }

    protected function getActivityLabel(): string
    {
        return 'Documento';
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
        return $query->with(['activity.documentation', 'activity.program'])
            ->whereHas('activity', function ($query) {
                $query->where('tipo', 'documentacion')
                      ->orWhereHas('documentation');
            });
    }

    protected function getTableColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('activity.nombre')
                    ->label('Documento')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('activity.component.program.nombre')
                    ->label('Programa')
                    ->icon('heroicon-m-rectangle-stack')
                    ->color('gray'),

                Split::make([
                    Tables\Columns\TextColumn::make('activity.documentation.version')
                        ->default('v1.0')
                        ->formatStateUsing(fn ($state) => "v{$state}")
                        ->badge()
                        ->color('info'),
                    
                    Tables\Columns\TextColumn::make('estado')
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
                    \Filament\Forms\Components\Textarea::make('data.notas')
                        ->label('Versión Actualizada'),
                    \Filament\Forms\Components\DatePicker::make('data.fecha_aprobacion')
                        ->label('Fecha Aprobación')
                        ->default(now()),
                ]),
        ];
    }
}
