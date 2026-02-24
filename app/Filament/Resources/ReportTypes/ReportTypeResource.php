<?php

namespace App\Filament\Resources\ReportTypes;

use App\Filament\Resources\ReportTypes\Pages\CreateReportType;
use App\Filament\Resources\ReportTypes\Pages\EditReportType;
use App\Filament\Resources\ReportTypes\Pages\ListReportTypes;
use App\Filament\Resources\ReportTypes\Pages\ViewReportType;
use App\Filament\Resources\ReportTypes\Schemas\ReportTypeForm;
use App\Filament\Resources\ReportTypes\Schemas\ReportTypeInfolist;
use App\Filament\Resources\ReportTypes\Tables\ReportTypesTable;
use App\Models\ReportType;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReportTypeResource extends Resource
{
    protected static ?string $model = ReportType::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes y Seguimiento';

    public static function form(Schema $schema): Schema
    {
        return ReportTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReportTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportTypesTable::configure($table);
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
            'index' => ListReportTypes::route('/'),
            'create' => CreateReportType::route('/create'),
            'view' => ViewReportType::route('/{record}'),
            'edit' => EditReportType::route('/{record}/edit'),
        ];
    }
}
