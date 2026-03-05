<?php

namespace App\Filament\Resources\Promotions\Pages;

use App\Filament\Resources\Promotions\PromotionResource;
use App\Filament\Resources\Promotions\Widgets\PromotionsForToday;
use App\Filament\Resources\Promotions\Widgets\EventualPromotions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromotions extends ListRecords
{
    protected static string $resource = PromotionResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\Promotions\Widgets\PromotionStatsOverview::class,
            PromotionsForToday::class,
            EventualPromotions::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Promoción')
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $service = new \App\Services\ActivityService();
                    return $service->createWithType($data, 'promocion');
                }),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }
}
