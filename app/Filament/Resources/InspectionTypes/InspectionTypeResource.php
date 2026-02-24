<?php

namespace App\Filament\Resources\InspectionTypes;

use App\Filament\Resources\InspectionTypes\Pages\CreateInspectionType;
use App\Filament\Resources\InspectionTypes\Pages\EditInspectionType;
use App\Filament\Resources\InspectionTypes\Pages\ListInspectionTypes;
use App\Filament\Resources\InspectionTypes\Pages\ViewInspectionType;
use App\Filament\Resources\InspectionTypes\Schemas\InspectionTypeForm;
use App\Filament\Resources\InspectionTypes\Schemas\InspectionTypeInfolist;
use App\Filament\Resources\InspectionTypes\Tables\InspectionTypesTable;
use App\Models\InspectionType;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InspectionTypeResource extends Resource
{
    protected static ?string $model = InspectionType::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|UnitEnum|null $navigationGroup = 'Evaluaciones y Auditorías';

    public static function form(Schema $schema): Schema
    {
        return InspectionTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InspectionTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InspectionTypesTable::configure($table);
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
            'index' => ListInspectionTypes::route('/'),
            'create' => CreateInspectionType::route('/create'),
            'view' => ViewInspectionType::route('/{record}'),
            'edit' => EditInspectionType::route('/{record}/edit'),
        ];
    }
}
