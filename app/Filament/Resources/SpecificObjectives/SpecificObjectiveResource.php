<?php

namespace App\Filament\Resources\SpecificObjectives;

use App\Filament\Resources\SpecificObjectives\Pages\CreateSpecificObjective;
use App\Filament\Resources\SpecificObjectives\Pages\EditSpecificObjective;
use App\Filament\Resources\SpecificObjectives\Pages\ListSpecificObjectives;
use App\Filament\Resources\SpecificObjectives\Pages\ViewSpecificObjective;
use App\Filament\Resources\SpecificObjectives\Schemas\SpecificObjectiveForm;
use App\Filament\Resources\SpecificObjectives\Schemas\SpecificObjectiveInfolist;
use App\Filament\Resources\SpecificObjectives\Tables\SpecificObjectivesTable;
use App\Models\SpecificObjective;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SpecificObjectiveResource extends Resource
{
    protected static ?string $model = SpecificObjective::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Elementos';

    public static function form(Schema $schema): Schema
    {
        return SpecificObjectiveForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SpecificObjectiveInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpecificObjectivesTable::configure($table);
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
            'index' => ListSpecificObjectives::route('/'),
            'create' => CreateSpecificObjective::route('/create'),
            'view' => ViewSpecificObjective::route('/{record}'),
            'edit' => EditSpecificObjective::route('/{record}/edit'),
        ];
    }
}
