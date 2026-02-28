<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ProgramInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles del Programa')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('nombre')
                                    ->label('Nombre'),
                                TextEntry::make('anio')
                                    ->label('Año'),
                                TextEntry::make('estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'borrador' => 'gray',
                                        'aprobado' => 'success',
                                        'cerrado' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('supervisor.nombre')
                                    ->label('Supervisor'),
                            ]),
                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ])
            ]);
    }
}
