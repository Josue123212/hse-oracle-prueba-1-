<?php

namespace App\Filament\Resources\OperationalControls;

use App\Filament\Resources\OperationalControls\Pages\CreateOperationalControl;
use App\Filament\Resources\OperationalControls\Pages\EditOperationalControl;
use App\Filament\Resources\OperationalControls\Pages\ListOperationalControls;
use App\Filament\Resources\OperationalControls\Schemas\OperationalControlForm;
use App\Filament\Resources\OperationalControls\Tables\OperationalControlsTable;
use App\Models\OperationalControl;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OperationalControlResource extends Resource
{
    protected static ?string $model = OperationalControl::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog';

    protected static string|UnitEnum|null $navigationGroup = 'Operaciones y Control';

    protected static ?string $recordTitleAttribute = 'nombre_proceso';

    public static function form(Schema $schema): Schema
    {
        return OperationalControlForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OperationalControlsTable::configure($table);
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
            'index' => ListOperationalControls::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nombre_proceso',
            'parametro',
            'observaciones',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Parámetro' => $record->parametro ?? '',
            'Valor Esperado' => $record->valor_esperado ?? '',
            'Estado' => ucfirst($record->estado) ?? '',
            'Fecha' => $record->fecha_programada?->format('d/m/Y') ?? '',
        ];
    }
}
