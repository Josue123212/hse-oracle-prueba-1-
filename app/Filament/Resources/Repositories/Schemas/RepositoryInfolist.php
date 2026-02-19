<?php

namespace App\Filament\Resources\Repositories\Schemas;

use Filament\Schemas\Schema;

class RepositoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Infolists\Components\TextEntry::make('nombre')
                    ->label('Nombre'),
                \Filament\Infolists\Components\TextEntry::make('estado')
                    ->label('Estado'),
                \Filament\Infolists\Components\TextEntry::make('url')
                    ->label('Ruta/URL'),
                \Filament\Infolists\Components\TextEntry::make('repositoriable_type')
                    ->label('Tipo asociado'),
                \Filament\Infolists\Components\TextEntry::make('repositoriable_id')
                    ->label('ID asociado'),
                \Filament\Infolists\Components\TextEntry::make('user.name')
                    ->label('Responsable'),
                \Filament\Infolists\Components\TextEntry::make('fecha_creacion')
                    ->label('Fecha creación'),
                \Filament\Infolists\Components\TextEntry::make('ultima_verificacion')
                    ->label('Última verificación'),
            ]);
    }
}
