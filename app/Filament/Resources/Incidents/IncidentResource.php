<?php

namespace App\Filament\Resources\Incidents;

use App\Filament\Resources\Incidents\Pages\CreateIncident;
use App\Filament\Resources\Incidents\Pages\EditIncident;
use App\Filament\Resources\Incidents\Pages\ListIncidents;
use App\Filament\Resources\Incidents\Schemas\IncidentForm;
use App\Filament\Resources\Incidents\Tables\IncidentsTable;
use App\Models\Incident;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|UnitEnum|null $navigationGroup = 'Seguridad y Emergencias';

    protected static ?string $navigationLabel = 'Incidentes';

    protected static ?string $modelLabel = 'Incidente';

    protected static ?string $pluralModelLabel = 'Incidentes';

    protected static ?string $recordTitleAttribute = 'titulo';

    public static function form(Schema $schema): Schema
    {
        return IncidentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncidentsTable::configure($table);
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
            'index' => ListIncidents::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'titulo',
            'descripcion',
            'lugar',
            'causa_raiz',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Severidad' => ucfirst($record->severidad) ?? '',
            'Estado' => ucfirst($record->estado) ?? '',
            'Fecha Ocurrencia' => $record->fecha_ocurrencia?->format('d/m/Y H:i') ?? '',
        ];
    }
}
