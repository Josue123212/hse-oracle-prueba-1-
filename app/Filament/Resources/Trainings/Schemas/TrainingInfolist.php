<?php

namespace App\Filament\Resources\Trainings\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;

class TrainingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle de Capacitación')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tema')
                                    ->label('Tema')
                                    ->columnSpan(2),
                                
                                TextEntry::make('program.nombre')
                                    ->label('Programa'),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('responsable.nombre')
                                    ->label('Cargo Responsable'),

                                TextEntry::make('fecha_programada')
                                    ->label('Fecha Programada')
                                    ->date('d/m/Y'),
                                
                                TextEntry::make('hora_programada')
                                    ->label('Hora'),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('frecuencia')
                                    ->label('Frecuencia')
                                    ->formatStateUsing(fn ($state): string => ucfirst($state ?? ''))
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'programado' => 'warning',
                                        'ejecutado' => 'success',
                                        'cancelado' => 'danger',
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
