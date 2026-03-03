<?php

namespace App\Filament\Resources\Drills\Pages;

use App\Filament\Resources\Drills\DrillResource;
use App\Services\ActivityService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDrill extends CreateRecord
{
    protected static string $resource = DrillResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = new ActivityService();
        return $service->createWithType($data, 'simulacro');
    }
}
