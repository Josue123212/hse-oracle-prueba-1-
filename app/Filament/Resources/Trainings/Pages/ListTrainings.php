<?php

namespace App\Filament\Resources\Trainings\Pages;

use App\Filament\Resources\Trainings\TrainingResource;
use App\Filament\Resources\Trainings\Widgets\TrainingStatsOverview;
use App\Filament\Resources\Trainings\Widgets\TrainingsForToday;
use App\Filament\Resources\Trainings\Widgets\EventualTrainings;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainings extends ListRecords
{
    protected static string $resource = TrainingResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            TrainingsForToday::class,
            EventualTrainings::class,
            TrainingStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
