<?php

namespace App\Filament\Resources\Committees;

use App\Filament\Resources\Committees\Pages\CreateCommittee;
use App\Filament\Resources\Committees\Pages\EditCommittee;
use App\Filament\Resources\Committees\Pages\ListCommittees;
use App\Filament\Resources\Committees\Schemas\CommitteeForm;
use App\Filament\Resources\Committees\Tables\CommitteesTable;
use App\Models\Committee;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CommitteeResource extends Resource
{
    protected static ?string $model = Committee::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión Administrativa';

    protected static ?string $navigationLabel = 'Comités';

    protected static ?string $modelLabel = 'Comité';

    protected static ?string $pluralModelLabel = 'Comités';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return CommitteeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommitteesTable::configure($table);
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
            'index' => ListCommittees::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nombre',
            'tema_principal',
            'acuerdos',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Tema' => $record->tema_principal ?? '',
            'Estado' => ucfirst($record->estado) ?? '',
            'Fecha' => $record->fecha_programada?->format('d/m/Y') ?? '',
        ];
    }
}
