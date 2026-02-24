<?php

namespace App\Filament\Resources\TrainingMaterials;

use App\Filament\Resources\TrainingMaterials\Pages\CreateTrainingMaterial;
use App\Filament\Resources\TrainingMaterials\Pages\EditTrainingMaterial;
use App\Filament\Resources\TrainingMaterials\Pages\ListTrainingMaterials;
use App\Filament\Resources\TrainingMaterials\Pages\ViewTrainingMaterial;
use App\Filament\Resources\TrainingMaterials\Schemas\TrainingMaterialForm;
use App\Filament\Resources\TrainingMaterials\Schemas\TrainingMaterialInfolist;
use App\Filament\Resources\TrainingMaterials\Tables\TrainingMaterialsTable;
use App\Models\TrainingMaterial;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TrainingMaterialResource extends Resource
{
    protected static ?string $model = TrainingMaterial::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|UnitEnum|null $navigationGroup = 'Capacitación';

    public static function form(Schema $schema): Schema
    {
        return TrainingMaterialForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TrainingMaterialInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingMaterialsTable::configure($table);
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
            'index' => ListTrainingMaterials::route('/'),
            'create' => CreateTrainingMaterial::route('/create'),
            'view' => ViewTrainingMaterial::route('/{record}'),
            'edit' => EditTrainingMaterial::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nombre',
            'url_material',
            'training.titulo',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Capacitación' => $record->training->titulo ?? '',
            'URL' => $record->url_material ?? '',
        ];
    }
}
