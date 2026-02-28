<?php

namespace App\Filament\Resources\Trainings;

use App\Filament\Resources\Trainings\Pages\CreateTraining;
use App\Filament\Resources\Trainings\Pages\EditTraining;
use App\Filament\Resources\Trainings\Pages\ListTrainings;
use App\Filament\Resources\Trainings\Pages\ViewTraining;
use App\Filament\Resources\Trainings\Schemas\TrainingForm;
use App\Filament\Resources\Trainings\Schemas\TrainingInfolist;
use App\Filament\Resources\Trainings\Tables\TrainingsTable;
use App\Filament\Resources\Trainings\Widgets\TrainingStatsOverview;
use App\Models\Training;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $recordTitleAttribute = 'tema';

    protected static string|UnitEnum|null $navigationGroup = 'Cultura y Formación';

    public static function form(Schema $schema): Schema
    {
        return TrainingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TrainingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingsTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            TrainingStatsOverview::class,
        ];
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
            'index' => ListTrainings::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'tema',
            'responsable.name',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Estado' => $record->estado ?? '',
            'Fecha' => $record->fecha_programada ?? '',
        ];
    }
}
