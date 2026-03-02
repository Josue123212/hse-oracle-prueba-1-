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

use App\Filament\Resources\Inspections\Widgets\InspectionStatsOverview;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Operaciones y Control';

    protected static ?string $navigationLabel = 'Inspecciones';

    protected static ?string $modelLabel = 'Inspección';

    protected static ?string $pluralModelLabel = 'Inspecciones';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function getWidgets(): array
    {
        return [
            InspectionStatsOverview::class,
        ];
    }

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
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nombre',
            'activity.nombre',
            'location.nombre',
            'responsable.nombre',
            'estado',
            'observaciones',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Actividad' => $record->activity->nombre ?? '',
            'Sede' => $record->location->nombre ?? '',
            'Inspector' => $record->responsable->nombre ?? '',
            'Estado' => ucfirst($record->estado) ?? '',
        ];
    }
}
