<?php

namespace App\Filament\Resources\Audits\Pages;

use App\Filament\Resources\Audits\AuditResource;
use App\Filament\Resources\Audits\Widgets\AuditStatsOverview;
use App\Filament\Resources\Audits\Widgets\AuditsForToday;
use App\Filament\Resources\Audits\Widgets\EventualAudits;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAudits extends ListRecords
{
    protected static string $resource = AuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AuditsForToday::class,
            EventualAudits::class,
            AuditStatsOverview::class,
        ];
    }
}
