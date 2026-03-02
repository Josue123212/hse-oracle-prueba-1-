<?php

namespace App\Filament\Resources\Promotions;

use App\Filament\Resources\Promotions\Pages\CreatePromotion;
use App\Filament\Resources\Promotions\Pages\EditPromotion;
use App\Filament\Resources\Promotions\Pages\ListPromotions;
use App\Filament\Resources\Promotions\Schemas\PromotionForm;
use App\Filament\Resources\Promotions\Tables\PromotionsTable;
use App\Models\Promotion;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|UnitEnum|null $navigationGroup = 'Cultura y Formación';

    protected static ?string $navigationLabel = 'Promociones';

    protected static ?string $modelLabel = 'Promoción';

    protected static ?string $pluralModelLabel = 'Promociones';

    protected static ?string $recordTitleAttribute = 'nombre_campana';

    public static function form(Schema $schema): Schema
    {
        return PromotionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromotionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotions::route('/'),
            'create' => CreatePromotion::route('/create'),
            'edit' => EditPromotion::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nombre_campana',
            'descripcion',
            'publico_objetivo',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Nombre' => $record->nombre_campana ?? '',
            'Estado' => ucfirst($record->estado) ?? '',
            'Público' => $record->publico_objetivo ?? '',
            'Fecha Programada' => $record->fecha_programada?->format('d/m/Y') ?? '',
        ];
    }
}
