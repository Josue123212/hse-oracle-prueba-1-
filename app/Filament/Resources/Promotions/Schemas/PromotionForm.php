<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Filament\Resources\Activities\Schemas\BaseActivityForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Activity;

class PromotionForm extends BaseActivityForm
{
    public static function getSpecificFields(): array
    {
        return [
            Select::make('activity_id')
                ->label('Actividad Relacionada')
                ->options(fn (Get $get) => Activity::whereHas('component', fn ($query) => $query->where('program_id', $get('program_id')))->pluck('nombre', 'id'))
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    if ($state) {
                        $activity = Activity::find($state);
                        if ($activity) {
                            $set('nombre_campana', $activity->nombre);
                            $set('frecuencia', $activity->frecuencia);
                            
                            $map = [
                                'diario' => 365,
                                'semanal' => 52,
                                'mensual' => 12,
                                'trimestral' => 4,
                                'semestral' => 2,
                                'anual' => 1,
                                'eventual' => 1,
                            ];
                            $set('veces_al_anio', $map[$activity->frecuencia] ?? 1);
                            $set('ejecuciones_realizadas', $activity->ejecuciones_realizadas ?? 0);
                        }
                    } else {
                        $set('nombre_campana', null);
                    }
                }),

            TextInput::make('nombre_campana')
                ->label('Nombre de la Campaña')
                ->required()
                ->maxLength(255),
        ];
    }
}
