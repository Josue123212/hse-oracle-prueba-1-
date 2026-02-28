<?php

namespace App\Filament\Resources\Committees\Schemas;

use App\Enums\ActivityState;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use Filament\Schemas\Components\Utilities\Get;

class CommitteeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('program_id')
                    ->relationship('program', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Programa'),
                
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre de la Reunión'),

                TextInput::make('tema_principal')
                    ->maxLength(255)
                    ->label('Tema Principal'),

                DatePicker::make('fecha_programada')
                    ->required()
                    ->label('Fecha Inicial')
                    ->live(),

                Select::make('frecuencia')
                    ->label('Frecuencia')
                    ->options([
                        'diario' => 'Diario',
                        'semanal' => 'Semanal',
                        'mensual' => 'Mensual',
                        'trimestral' => 'Trimestral',
                        'semestral' => 'Semestral',
                        'anual' => 'Anual',
                        'eventual' => 'Eventualmente',
                    ])
                    ->live()
                    ->afterStateUpdated(function ($set, ?string $state) {
                        $map = [
                            'diario' => 365,
                            'semanal' => 52,
                            'mensual' => 12,
                            'trimestral' => 4,
                            'semestral' => 2,
                            'anual' => 1,
                            'eventual' => 1,
                        ];
                        $set('veces_al_anio', $map[$state] ?? 1);
                        $set('ejecuciones_realizadas', 0);
                    }),

                TextInput::make('veces_al_anio')
                    ->label('Veces al Año')
                    ->numeric()
                    ->readOnly()
                    ->default(1)
                    ->hidden(),

                TextInput::make('ejecuciones_realizadas')
                    ->label('Ejecuciones Realizadas')
                    ->numeric()
                    ->readOnly()
                    ->default(0)
                    ->hidden(),

                TextInput::make('detalle_frecuencia')
                    ->label('Detalle de Eventualidad')
                    ->placeholder('Especifique la razón (ej. Cuando ocurra, Personal nuevo)')
                    ->required(fn (Get $get) => $get('frecuencia') === 'eventual')
                    ->visible(fn (Get $get) => $get('frecuencia') === 'eventual')
                    ->columnSpanFull(),

                Placeholder::make('fechas_programadas_visual')
                    ->label('Cronograma')
                    ->content(function (Get $get) {
                        $fechaInicio = $get('fecha_programada');
                        $frecuencia = $get('frecuencia');
                        $vecesAlAnio = (int) $get('veces_al_anio');

                        if (!$fechaInicio || !$frecuencia) {
                            return new HtmlString('<span class="text-gray-500 italic">Seleccione fecha programada y frecuencia para ver el cronograma.</span>');
                        }

                        if ($frecuencia === 'eventual' || $vecesAlAnio === 0) {
                            $detalle = $get('detalle_frecuencia') ? ': ' . htmlspecialchars($get('detalle_frecuencia')) : '';
                            if ($fechaInicio) {
                                try {
                                    $date = Carbon::parse($fechaInicio)->format('d/m/Y');
                                    return new HtmlString('<span class="text-gray-500 italic">Eventualmente' . $detalle . ' - <strong>Ejecutada el: ' . $date . '</strong></span>');
                                } catch (\Exception $e) {}
                            }
                            return new HtmlString('<span class="text-gray-500 italic">Eventualmente' . $detalle . '</span>');
                        }

                        try {
                            $date = Carbon::parse($fechaInicio);
                        } catch (\Exception $e) {
                            return 'Fecha inválida';
                        }

                        $fechas = [];
                        $limit = match($frecuencia) {
                            'diario' => 10,
                            'semanal' => 12,
                            'mensual' => 12,
                            'trimestral' => 4,
                            'semestral' => 2,
                            'anual' => 1,
                            'eventual' => 1,
                            default => 1
                        };

                        for ($i = 0; $i < $limit; $i++) {
                            $fechas[] = $date->format('d/m/Y');
                            match($frecuencia) {
                                'diario' => $date->addDay(),
                                'semanal' => $date->addWeek(),
                                'mensual' => $date->addMonth(),
                                'trimestral' => $date->addMonths(3),
                                'semestral' => $date->addMonths(6),
                                'anual' => $date->addYear(),
                                default => null,
                            };
                        }

                        $html = '<div class="grid grid-cols-2 md:grid-cols-4 gap-2">';
                        foreach ($fechas as $f) {
                            $html .= "<div class='bg-gray-100 dark:bg-gray-800 p-2 rounded text-center text-sm'>{$f}</div>";
                        }
                        if ($frecuencia === 'diario' && $limit === 10) {
                            $html .= "<div class='bg-gray-100 dark:bg-gray-800 p-2 rounded text-center text-sm'>...</div>";
                        }
                        $html .= '</div>';

                        return new HtmlString($html);
                    })
                    ->columnSpanFull(),

                Select::make('responsable_id')
                    ->relationship('responsable', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Responsable'),

                Select::make('estado')
                    ->label('Estado')
                    ->options(ActivityState::class)
                    ->required()
                    ->default(ActivityState::PROGRAMADO->value),

                Textarea::make('acuerdos')
                    ->label('Acuerdos / Observaciones')
                    ->columnSpanFull(),
            ]);
    }
}
