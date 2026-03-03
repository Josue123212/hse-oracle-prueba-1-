<?php

namespace App\Filament\Resources\Documentations\Pages;

use App\Filament\Resources\Documentations\DocumentationResource;
use App\Services\ActivityService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDocumentation extends CreateRecord
{
    protected static string $resource = DocumentationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = new ActivityService();
        return $service->createWithType($data, 'documentacion');
    }
}
