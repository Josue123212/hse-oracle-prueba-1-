<?php

namespace App\Filament\Resources\Supervisors\Pages;

use App\Filament\Resources\Supervisors\SupervisorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSupervisor extends EditRecord
{
    protected static string $resource = SupervisorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
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
