<?php

namespace App\Filament\Resources\Inspections\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;

class InspectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles de la Inspección')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('program.nombre')
                                    ->label('Programa'),
                                
                                TextEntry::make('responsable.nombre')
                                    ->label('Cargo Responsable'),

                                TextEntry::make('fecha_programada')
                                    ->label('Fecha Programada')
                                    ->date('d/m/Y'),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'programado' => 'warning',
                                        'ejecutado' => 'success',
                                        'vencido' => 'danger',
                                        default => 'gray',
                                    }),
                                
                                TextEntry::make('resultado')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'aprobado' => 'success',
                                        'rechazado' => 'danger',
                                        'pendiente' => 'warning',
                                        default => 'gray',
                                    }),
                            ]),

                        TextEntry::make('observaciones')
                            ->columnSpanFull()
                            ->markdown(),
                    ])
            ]);
    }
}
