<?php

namespace App\Filament\Resources\Documentations\Pages;

use App\Filament\Resources\Documentations\DocumentationResource;
use App\Filament\Resources\Documentations\Widgets\DocumentsForToday;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Livewire;

class ListDocumentations extends ListRecords
{
    protected static string $resource = DocumentationResource::class;

    protected function getHeaderWidgets(): array
    {
        // Notificaciones específicas para documentaciones
        app(\App\Services\ActivityNotificationService::class)->checkAndNotify('documentacion');

        return [
            \App\Filament\Resources\Documentations\Widgets\DocumentationStatsOverview::class,
            DocumentsForToday::class,
            \App\Filament\Resources\Documentations\Widgets\EventualDocuments::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Documentación')
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $service = new \App\Services\ActivityService();
                    return $service->createWithType($data, 'documentacion');
                }),
        ];
    }
}
