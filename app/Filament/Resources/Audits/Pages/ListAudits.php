<?php

namespace App\Filament\Resources\Audits\Pages;

use App\Filament\Resources\Audits\AuditResource;
use App\Filament\Resources\Audits\Widgets\AuditStatsOverview;
use App\Filament\Resources\Audits\Widgets\AuditsForToday;
use App\Filament\Resources\Audits\Widgets\EventualAudits;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Traits\HasEvidencePreview;

class ListAudits extends ListRecords
{
    use HasEvidencePreview;

    protected static string $resource = AuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Auditoría')
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $service = new \App\Services\ActivityService();
                    return $service->createWithType($data, 'auditoria');
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        // Notificaciones específicas para auditorías
        app(\App\Services\ActivityNotificationService::class)->checkAndNotify('auditoria');

        return [
            AuditStatsOverview::class,
            AuditsForToday::class,
            EventualAudits::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }
}
