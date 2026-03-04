<?php

namespace App\Filament\Resources\ProgramComponents;

use App\Filament\Resources\ProgramComponents\Pages\ManageProgramComponents;
use App\Models\ProgramComponent;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use UnitEnum;
use BackedEnum;

class ProgramComponentResource extends Resource
{
    protected static ?string $model = ProgramComponent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Componentes';

    protected static ?string $modelLabel = 'Componente';

    protected static ?string $pluralModelLabel = 'Componentes';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Programas';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('program_id')
                    ->label('Programa')
                    ->relationship('program', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('parent_id', null)),

                Select::make('type')
                    ->label('Tipo de Componente')
                    ->options([
                        'subprograma' => 'Subprograma',
                        'elemento' => 'Elemento',
                    ])
                    ->required()
                    ->default('subprograma')
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('parent_id', null)),

                Select::make('parent_id')
                    ->label(fn (Get $get) => $get('type') === 'elemento' ? 'Padre (Subprograma o Elemento)' : 'Padre (Opcional)')
                    ->options(fn (Get $get) => ProgramComponent::query()
                        ->where('program_id', $get('program_id'))
                        ->when($get('type') === 'elemento', function ($query) {
                            // Si soy elemento, mi padre puede ser subprograma O elemento
                            return $query->whereIn('type', ['subprograma', 'elemento']);
                        }, function ($query) {
                            // Si soy subprograma, mi padre (si tuviera) solo podria ser subprograma
                            return $query->where('type', 'subprograma');
                        })
                        ->where('id', '!=', $get('id')) // Evitar auto-referencia
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->placeholder('Seleccione el componente superior')
                    ->visible(fn (Get $get) => $get('program_id') && $get('type')), // Solo mostrar si hay programa y tipo seleccionados

                TextInput::make('name')
                    ->label('Nombre'),

                Textarea::make('objetivo')
                    ->label('Objetivo Específico')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Componente')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('parent.name')
                    ->label('Padre')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Principal')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'primary' => 'subprograma',
                        'success' => 'elemento',
                    ]),

                TextColumn::make('activities_count')
                    ->label('Actividades')
                    ->counts('activities')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('program_id')
                    ->label('Filtrar por Programa')
                    ->relationship('program', 'nombre')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Filtrar por Tipo')
                    ->options([
                        'subprograma' => 'Subprograma',
                        'elemento' => 'Elemento',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProgramComponents::route('/'),
        ];
    }
}
