<?php

namespace App\Filament\Resources\TrainingAttendances;

use App\Filament\Resources\TrainingAttendances\Pages\CreateTrainingAttendance;
use App\Filament\Resources\TrainingAttendances\Pages\EditTrainingAttendance;
use App\Filament\Resources\TrainingAttendances\Pages\ListTrainingAttendances;
use App\Filament\Resources\TrainingAttendances\Pages\ViewTrainingAttendance;
use App\Filament\Resources\TrainingAttendances\Schemas\TrainingAttendanceForm;
use App\Filament\Resources\TrainingAttendances\Schemas\TrainingAttendanceInfolist;
use App\Filament\Resources\TrainingAttendances\Tables\TrainingAttendancesTable;
use App\Models\TrainingAttendance;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrainingAttendanceResource extends Resource
{
    protected static ?string $model = TrainingAttendance::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'fecha_inicio';

    protected static string|UnitEnum|null $navigationGroup = 'Cultura y Formación';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return TrainingAttendanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TrainingAttendanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingAttendancesTable::configure($table);
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
            'index' => ListTrainingAttendances::route('/'),
        ];
    }
}
