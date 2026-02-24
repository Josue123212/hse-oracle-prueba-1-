<?php

namespace App\Filament\Resources\Repositories;

use App\Filament\Resources\Repositories\Pages\CreateRepository;
use App\Filament\Resources\Repositories\Pages\EditRepository;
use App\Filament\Resources\Repositories\Pages\ListRepositories;
use App\Filament\Resources\Repositories\Pages\ViewRepository;
use App\Filament\Resources\Repositories\Schemas\RepositoryForm;
use App\Filament\Resources\Repositories\Schemas\RepositoryInfolist;
use App\Filament\Resources\Repositories\Tables\RepositoriesTable;
use App\Models\Repository;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RepositoryResource extends Resource
{
    protected static ?string $model = Repository::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Elementos';

    public static function form(Schema $schema): Schema
    {
        return RepositoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RepositoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepositoriesTable::configure($table);
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
            'index' => ListRepositories::route('/'),
            'create' => CreateRepository::route('/create'),
            'view' => ViewRepository::route('/{record}'),
            'edit' => EditRepository::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nombre',
            'url',
            'estado',
            'user.name',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'URL' => $record->url ?? '',
            'Estado' => $record->estado ?? '',
            'Usuario' => $record->user->name ?? '',
        ];
    }
}
