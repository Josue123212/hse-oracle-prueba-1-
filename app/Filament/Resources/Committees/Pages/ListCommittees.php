<?php

namespace App\Filament\Resources\Committees\Pages;

use App\Filament\Resources\Committees\CommitteeResource;
use App\Filament\Resources\Committees\Widgets\CommitteesForToday;
use App\Filament\Resources\Committees\Widgets\EventualCommittees;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommittees extends ListRecords
{
    protected static string $resource = CommitteeResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CommitteesForToday::class,
            EventualCommittees::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
