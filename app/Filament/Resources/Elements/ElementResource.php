<?php

namespace App\Filament\Resources\Elements;

use App\Filament\Resources\Elements\Pages\CreateElement;
use App\Filament\Resources\Elements\Pages\EditElement;
use App\Filament\Resources\Elements\Pages\ListElements;
use App\Filament\Resources\Elements\Pages\ViewElement;
use App\Filament\Resources\Elements\Schemas\ElementForm;
use App\Filament\Resources\Elements\Schemas\ElementInfolist;
use App\Filament\Resources\Elements\Tables\ElementsTable;
use App\Models\Element;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ElementResource extends Resource
{
    protected static ?string $model = Element::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return ElementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ElementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ElementsTable::configure($table);
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
            'index' => ListElements::route('/'),
            'create' => CreateElement::route('/create'),
            'view' => ViewElement::route('/{record}'),
            'edit' => EditElement::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nombre',
            'program.codigo',
            'activity.codigo',
            'activity.nombre',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Programa' => $record->program->codigo ?? '',
            'Actividad' => $record->activity->nombre ?? '',
        ];
    }
}
