<?php

namespace App\Filament\Resources\Positions\Pages;

use App\Filament\Resources\Positions\PositionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPosition extends EditRecord
{
    protected static string $resource = PositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['tipo_firma']) && $data['tipo_firma'] === 'digital') {
            $data['firma'] = $data['firma_digital'] ?? null;
        }

        unset($data['tipo_firma']);
        unset($data['firma_digital']);

        return $data;
    }
}
