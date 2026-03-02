<?php

namespace App\Filament\Resources\PositionTypes;

use App\Filament\Resources\PositionTypes\Pages\CreatePositionType;
use App\Filament\Resources\PositionTypes\Pages\EditPositionType;
use App\Filament\Resources\PositionTypes\Pages\ListPositionTypes;
use App\Filament\Resources\PositionTypes\Pages\ViewPositionType;
use App\Filament\Resources\PositionTypes\Schemas\PositionTypeForm;
use App\Filament\Resources\PositionTypes\Schemas\PositionTypeInfolist;
use App\Filament\Resources\PositionTypes\Tables\PositionTypesTable;
use App\Models\PositionType;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PositionTypeResource extends Resource
{
    protected static ?string $model = PositionType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|UnitEnum|null $navigationGroup = 'Organización y Ubicaciones';

    protected static ?string $navigationLabel = 'Tipos de Cargos';

    protected static ?string $modelLabel = 'Tipo de Cargo';

    protected static ?string $pluralModelLabel = 'Tipos de Cargos';

    public static function form(Schema $schema): Schema
    {
        return PositionTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PositionTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PositionTypesTable::configure($table);
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
            'index' => ListPositionTypes::route('/'),
        ];
    }
}
