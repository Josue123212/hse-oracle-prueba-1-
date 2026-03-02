<?php

namespace App\Filament\Resources\Positions\Pages;

use App\Filament\Resources\Positions\PositionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosition extends CreateRecord
{
    protected static string $resource = PositionResource::class;
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
