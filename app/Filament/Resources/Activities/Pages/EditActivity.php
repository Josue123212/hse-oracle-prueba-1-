<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use App\Services\ActivityService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Filament\Notifications\Notification;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Eliminar Actividad')
                ->modalDescription('¿Estás seguro de que deseas eliminar esta actividad? Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar')
                ->successNotificationTitle('Actividad eliminada correctamente'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Actividad actualizada correctamente')
            ->body('Los cambios se han guardado en el sistema.')
            ->duration(5000);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Esta lógica dependía de que el formulario tuviera campos prefijados (ej. audit_auditor_id).
        // Si el formulario ActivityForm no tiene esos campos, esto es irrelevante por ahora.
        // Se mantiene simplificado para pasar los datos tal cual vienen del modelo.
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = new ActivityService();
        return $service->updateActivity($record, $data);
    }
}
