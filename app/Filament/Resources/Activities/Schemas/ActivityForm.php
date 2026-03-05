<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

class ActivityForm extends BaseActivityForm
{
    public static function getSpecificFields(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('tipo')
                        ->label('Tipo de Actividad')
                        ->options([
                            'general' => 'General',
                            'auditoria' => 'Auditoría',
                            'inspeccion' => 'Inspección',
                            'capacitacion' => 'Capacitación',
                            'simulacro' => 'Simulacro',
                            'incidente' => 'Incidente',
                            'comite' => 'Comité',
                            'documentacion' => 'Documentación',
                            'promocion' => 'Promoción',
                            'control_operacional' => 'Control Operacional',
                        ])
                        ->default('general')
                        ->live()
                        ->required(),

                    TextInput::make('nombre')
                        ->label('Nombre de la Actividad')
                        ->required()
                        ->placeholder('ej. Charla de 5 minutos'),
                ]),
        ];
    }
}