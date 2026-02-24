<?php

namespace App\Filament\Resources\QuestionAlternatives;

use App\Filament\Resources\QuestionAlternatives\Pages\CreateQuestionAlternative;
use App\Filament\Resources\QuestionAlternatives\Pages\EditQuestionAlternative;
use App\Filament\Resources\QuestionAlternatives\Pages\ListQuestionAlternatives;
use App\Filament\Resources\QuestionAlternatives\Schemas\QuestionAlternativeForm;
use App\Filament\Resources\QuestionAlternatives\Tables\QuestionAlternativesTable;
use App\Models\QuestionAlternative;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

class QuestionAlternativeResource extends Resource
{
    protected static ?string $model = QuestionAlternative::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'descripcion';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    public static function form(Schema $schema): Schema
    {
        return QuestionAlternativeForm::configure($schema);
    }


    public static function table(Table $table): Table
    {
        return QuestionAlternativesTable::configure($table);
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
            'index' => ListQuestionAlternatives::route('/'),
            'create' => CreateQuestionAlternative::route('/create'),

            'edit' => EditQuestionAlternative::route('/{record}/edit'),
        ];
    }
}
