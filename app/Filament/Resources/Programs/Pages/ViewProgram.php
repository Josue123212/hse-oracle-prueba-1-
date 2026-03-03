<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProgram extends ViewRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            \Filament\Actions\ActionGroup::make([
                \Filament\Actions\Action::make('pdf')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn () => route('programs.pdf', $this->record))
                    ->openUrlInNewTab(),
                \Filament\Actions\Action::make('excel')
                    ->label('Descargar Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn () => route('programs.excel', $this->record))
                    ->openUrlInNewTab(),
            ])
            ->label('Reportes')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('info')
            ->button(),
        ];
    }
}
