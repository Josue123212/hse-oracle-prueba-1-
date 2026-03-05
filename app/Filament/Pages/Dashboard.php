<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Session;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Panel de Control';
    protected static ?string $title = 'Panel de Control';

    protected function getHeaderActions(): array
    {
        $programsCount = \App\Models\Program::count();
        $singleProgramId = $programsCount === 1 ? \App\Models\Program::first()->id : null;

        return [
            ActionGroup::make([
                Action::make('pdf')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn () => route('programs.pdf', ['program' => Session::get('hse_program_id', $singleProgramId)]))
                    ->openUrlInNewTab(),
                Action::make('excel')
                    ->label('Descargar Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn () => route('programs.excel', ['program' => Session::get('hse_program_id', $singleProgramId)]))
                    ->openUrlInNewTab(),
            ])
            ->label('Reporte del Programa')
            ->icon('heroicon-o-document-arrow-down')
            ->button()
            ->visible(fn () => Session::has('hse_program_id') || $programsCount === 1),
        ];
    }
}
