<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use App\Filament\Resources\Programs\Widgets\ProgramStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrograms extends ListRecords
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderWidgets(): array
    {
        // Notificaciones generales del programa (si aplica) o específicas
        // Por ahora lo dejamos genérico o sin notificaciones si no se requiere
        return [
            ProgramStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Programa'),
        ];
    }
}
