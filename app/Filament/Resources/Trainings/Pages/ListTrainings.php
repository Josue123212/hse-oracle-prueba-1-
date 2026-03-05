<?php

namespace App\Filament\Resources\Trainings\Pages;

use App\Filament\Resources\Trainings\TrainingResource;
use App\Filament\Resources\Trainings\Widgets\TrainingStatsOverview;
use App\Filament\Resources\Trainings\Widgets\TrainingsForToday;
use App\Filament\Resources\Trainings\Widgets\EventualTrainings;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Traits\HasEvidencePreview;

class ListTrainings extends ListRecords
{
    use HasEvidencePreview;

    protected static string $resource = TrainingResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            TrainingStatsOverview::class,
            TrainingsForToday::class,
            EventualTrainings::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Capacitación')
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $service = new \App\Services\ActivityService();
                    return $service->createWithType($data, 'capacitacion');
                }),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }
}
