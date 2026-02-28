<?php

namespace App\Filament\Resources\Audits\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;

class AuditInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('program.nombre')
                                    ->label('Programa Asociado'),

                                TextEntry::make('nombre')
                                    ->label('Nombre de la Auditoría'),

                                TextEntry::make('auditor.name')
                                    ->label('Auditor Responsable')
                                    ->placeholder('Sin asignar'),
                            ]),
                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),

                Section::make('Programación y Estado')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('fecha_programada')
                                    ->label('Fecha Programada')
                                    ->date('d/m/Y'),

                                TextEntry::make('estado')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'programado' => 'info',
                                        'ejecutado' => 'success',
                                        'vencido' => 'danger',
                                        'reprogramado' => 'warning',
                                        default => 'gray',
                                    }),

                                TextEntry::make('frecuencia')
                                    ->label('Frecuencia')
                                    ->state(fn ($record) => $record->frecuencia ?? $record->activity?->frecuencia ?? 'N/A')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ]),

                Section::make('Resultados')
                    ->schema([
                        TextEntry::make('hallazgos')
                            ->label('Hallazgos y Conclusiones')
                            ->columnSpanFull()
                            ->markdown()
                            ->placeholder('Sin hallazgos registrados'),
                    ]),
            ]);
    }
}
