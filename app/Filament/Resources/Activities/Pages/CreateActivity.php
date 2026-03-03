<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use App\Services\ActivityService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = new ActivityService();
        $result = $service->createWithType($data, $data['tipo']);
        
        // If result is already an Activity (e.g. 'general' type), return it directly
        if ($result instanceof \App\Models\Activity) {
            return $result;
        }

        // Otherwise return the associated activity from the satellite
        return $result->activity;
    }
}
