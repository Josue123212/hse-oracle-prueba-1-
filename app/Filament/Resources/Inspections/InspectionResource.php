<?php

namespace App\Filament\Resources\Inspections;

use App\Filament\Resources\Inspections\Pages\CreateInspection;
use App\Filament\Resources\Inspections\Pages\EditInspection;
use App\Filament\Resources\Inspections\Pages\ListInspections;
use App\Filament\Resources\Inspections\Pages\ViewInspection;
use App\Filament\Resources\Inspections\Schemas\InspectionForm;
use App\Filament\Resources\Inspections\Schemas\InspectionInfolist;
use App\Filament\Resources\Inspections\Tables\InspectionsTable;
use App\Models\Inspection;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'mes';

    protected static string|UnitEnum|null $navigationGroup = 'Evaluaciones y Auditorías';

    public static function form(Schema $schema): Schema
    {
        return InspectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InspectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InspectionsTable::configure($table);
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
            'index' => ListInspections::route('/'),
            'create' => CreateInspection::route('/create'),
            'view' => ViewInspection::route('/{record}'),
            'edit' => EditInspection::route('/{record}/edit'),
        ];
    }

     public static function getGloballySearchableAttributes(): array
    {
        return [
            'inspectionType.nombre',
            'activity.nombre',
            'location.nombre',
            'user.name',
            'estado',
            'observaciones',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Tipo' => $record->inspectionType->nombre ?? '',
            'Actividad' => $record->activity->nombre ?? '',
            'Sede' => $record->location->nombre ?? '',
            'Inspector' => $record->user->name ?? '',
            'Estado' => $record->estado ?? '',
        ];
    }
}
