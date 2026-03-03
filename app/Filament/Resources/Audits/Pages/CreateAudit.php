<?php

namespace App\Filament\Resources\Audits\Pages;

use App\Filament\Resources\Audits\AuditResource;
use App\Services\ActivityService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAudit extends CreateRecord
{
    protected static string $resource = AuditResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = new ActivityService();
        return $service->createWithType($data, 'auditoria');
    }
}
