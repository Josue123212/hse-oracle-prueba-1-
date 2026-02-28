<?php

namespace App\Filament\Resources\Drills;

use App\Filament\Resources\Drills\Pages\CreateDrill;
use App\Filament\Resources\Drills\Pages\EditDrill;
use App\Filament\Resources\Drills\Pages\ListDrills;
use App\Filament\Resources\Drills\Schemas\DrillForm;
use App\Filament\Resources\Drills\Tables\DrillsTable;
use App\Models\Drill;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DrillResource extends Resource
{
    protected static ?string $model = Drill::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static string|UnitEnum|null $navigationGroup = 'Seguridad y Emergencias';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return DrillForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DrillsTable::configure($table);
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
            'index' => ListDrills::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nombre',
            'escenario',
            'descripcion',
            'evaluacion',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Estado' => $record->estado ?? '',
            'Fecha Programada' => $record->fecha_programada?->format('d/m/Y') ?? '',
        ];
    }
}
