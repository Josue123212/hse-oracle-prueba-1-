<?php

namespace App\Filament\Resources\ElementTypes;

use App\Filament\Resources\ElementTypes\Pages\CreateElementType;
use App\Filament\Resources\ElementTypes\Pages\EditElementType;
use App\Filament\Resources\ElementTypes\Pages\ListElementTypes;
use App\Filament\Resources\ElementTypes\Pages\ViewElementType;
use App\Filament\Resources\ElementTypes\Schemas\ElementTypeForm;
use App\Filament\Resources\ElementTypes\Schemas\ElementTypeInfolist;
use App\Filament\Resources\ElementTypes\Tables\ElementTypesTable;
use App\Models\ElementType;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ElementTypeResource extends Resource
{
    protected static ?string $model = ElementType::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Elementos';

    public static function form(Schema $schema): Schema
    {
        return ElementTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ElementTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ElementTypesTable::configure($table);
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
            'index' => ListElementTypes::route('/'),
            'create' => CreateElementType::route('/create'),
            'view' => ViewElementType::route('/{record}'),
            'edit' => EditElementType::route('/{record}/edit'),
        ];
    }
}
