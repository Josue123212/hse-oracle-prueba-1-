<?php

namespace App\Filament\Resources\ExamResults;

use App\Filament\Resources\ExamResults\Pages\CreateExamResult;
use App\Filament\Resources\ExamResults\Pages\EditExamResult;
use App\Filament\Resources\ExamResults\Pages\ListExamResults;
use App\Filament\Resources\ExamResults\Pages\ViewExamResult;
use App\Filament\Resources\ExamResults\Schemas\ExamResultForm;
use App\Filament\Resources\ExamResults\Schemas\ExamResultInfolist;
use App\Filament\Resources\ExamResults\Tables\ExamResultsTable;
use App\Models\ExamResult;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExamResultResource extends Resource
{
    protected static ?string $model = ExamResult::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'nota';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    public static function form(Schema $schema): Schema
    {
        return ExamResultForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExamResultInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamResultsTable::configure($table);
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
            'index' => ListExamResults::route('/'),
            'create' => CreateExamResult::route('/create'),
            'view' => ViewExamResult::route('/{record}'),
            'edit' => EditExamResult::route('/{record}/edit'),
        ];
    }
}
