<?php

namespace App\Filament\Resources\OperationalControls\Pages;

use App\Filament\Resources\OperationalControls\OperationalControlResource;
use App\Services\ActivityService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOperationalControl extends CreateRecord
{
    protected static string $resource = OperationalControlResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = new ActivityService();
        return $service->createWithType($data, 'control_operacional');
    }
}
