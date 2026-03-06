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
        // Notificaciones específicas para comités
        app(\App\Services\ActivityNotificationService::class)->checkAndNotify('comite');

        return [
            \App\Filament\Resources\Committees\Widgets\CommitteeStatsOverview::class,
            CommitteesForToday::class,
            EventualCommittees::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Comité')
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $service = new \App\Services\ActivityService();
                    return $service->createWithType($data, 'comite');
                }),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }
}
