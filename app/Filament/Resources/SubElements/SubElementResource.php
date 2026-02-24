<?php

namespace App\Filament\Resources\SubElements;

use App\Filament\Resources\SubElements\Pages\CreateSubElement;
use App\Filament\Resources\SubElements\Pages\EditSubElement;
use App\Filament\Resources\SubElements\Pages\ListSubElements;
use App\Filament\Resources\SubElements\Pages\ViewSubElement;
use App\Filament\Resources\SubElements\Schemas\SubElementForm;
use App\Filament\Resources\SubElements\Schemas\SubElementInfolist;
use App\Filament\Resources\SubElements\Tables\SubElementsTable;
use App\Models\SubElement;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubElementResource extends Resource
{
    protected static ?string $model = SubElement::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Elementos';

    public static function form(Schema $schema): Schema
    {
        return SubElementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubElementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubElementsTable::configure($table);
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
            'index' => ListSubElements::route('/'),
            'create' => CreateSubElement::route('/create'),
            'view' => ViewSubElement::route('/{record}'),
            'edit' => EditSubElement::route('/{record}/edit'),
        ];
    }
}
