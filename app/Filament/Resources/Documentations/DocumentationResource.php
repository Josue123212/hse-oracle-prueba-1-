<?php

namespace App\Filament\Resources\Documentations;

use App\Filament\Resources\Documentations\Pages\CreateDocumentation;
use App\Filament\Resources\Documentations\Pages\EditDocumentation;
use App\Filament\Resources\Documentations\Pages\ListDocumentations;
use App\Filament\Resources\Documentations\Schemas\DocumentationForm;
use App\Filament\Resources\Documentations\Tables\DocumentationsTable;
use App\Models\Documentation;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DocumentationResource extends Resource
{
    protected static ?string $model = Documentation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión Administrativa';

    protected static ?string $navigationLabel = 'Documentación';

    protected static ?string $modelLabel = 'Documento';

    protected static ?string $pluralModelLabel = 'Documentos';

    protected static ?string $recordTitleAttribute = 'titulo';

    public static function form(Schema $schema): Schema
    {
        return DocumentationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentationsTable::configure($table);
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
            'index' => ListDocumentations::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'titulo',
            'tipo_documento',
            'version',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Tipo' => $record->tipo_documento ?? '',
            'Versión' => $record->version ?? '',
            'Estado' => ucfirst($record->estado) ?? '',
        ];
    }
}
