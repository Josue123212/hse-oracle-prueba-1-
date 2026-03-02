<?php

namespace App\Filament\Resources\Supervisors;

use App\Filament\Resources\Supervisors\Pages\CreateSupervisor;
use App\Filament\Resources\Supervisors\Pages\EditSupervisor;
use App\Filament\Resources\Supervisors\Pages\ListSupervisors;
use App\Filament\Resources\Supervisors\Pages\ViewSupervisor;
use App\Filament\Resources\Supervisors\Schemas\SupervisorForm;
use App\Filament\Resources\Supervisors\Schemas\SupervisorInfolist;
use App\Filament\Resources\Supervisors\Tables\SupervisorsTable;
use App\Models\Supervisor;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupervisorResource extends Resource
{
    protected static ?string $model = Supervisor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static string|UnitEnum|null $navigationGroup = 'Organización y Ubicaciones';

    protected static ?string $navigationLabel = 'Supervisores';

    protected static ?string $modelLabel = 'Supervisor';

    protected static ?string $pluralModelLabel = 'Supervisores';

    public static function form(Schema $schema): Schema
    {
        return SupervisorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SupervisorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupervisorsTable::configure($table);
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
            'index' => ListSupervisors::route('/'),
        ];
    }
}
