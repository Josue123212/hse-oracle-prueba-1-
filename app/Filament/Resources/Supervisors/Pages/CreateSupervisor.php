<?php

namespace App\Filament\Resources\Supervisors\Pages;

use App\Filament\Resources\Supervisors\SupervisorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupervisor extends CreateRecord
{
    protected static string $resource = SupervisorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['tipo_firma']) && $data['tipo_firma'] === 'digital') {
            $data['firma'] = $data['firma_digital'] ?? null;
        }

        unset($data['tipo_firma']);
        unset($data['firma_digital']);

        return $data;
    }
}
