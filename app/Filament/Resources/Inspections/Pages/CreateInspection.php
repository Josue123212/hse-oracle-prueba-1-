<?php

namespace App\Filament\Resources\Inspections\Pages;

use App\Filament\Resources\Inspections\InspectionResource;
use App\Services\ActivityService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInspection extends CreateRecord
{
    protected static string $resource = InspectionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = new ActivityService();
        return $service->createWithType($data, 'inspeccion');
    }
}
