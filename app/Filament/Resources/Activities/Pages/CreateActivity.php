<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create($data);
    }
}
