<?php

namespace App\Filament\Resources\Programs\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ProgramComponent;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class ProgramComponentsRelationManager extends RelationManager
{
    protected static string $relationship = 'components';

    protected static ?string $title = 'Componentes / Elementos';

    protected static ?string $modelLabel = 'Componente';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->label('Componente Padre (Opcional)')
                    ->options(function ($livewire) {
                        // Get current program ID
                        $program = $livewire->getOwnerRecord();
                        // List components of this program, excluding self if editing (not easy here without record context)
                        // But for creation it's fine.
                        return ProgramComponent::where('program_id', $program->id)
                            ->whereNull('parent_id') // Only allow 1 level of nesting (Subprograma -> Elemento)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),
                
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                
                Textarea::make('objetivo')
                    ->label('Objetivo Específico')
                    ->rows(2)
                    ->columnSpanFull(),

                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'subprograma' => 'Subprograma',
                        'elemento' => 'Elemento',
                    ])
                    ->required()
                    ->default('subprograma'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nombre Completo')
                    ->searchable(['name'])
                    ->sortable(),
                
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'primary' => 'subprograma',
                        'success' => 'elemento',
                    ]),
                
                TextColumn::make('activities_count')
                    ->label('Actividades')
                    ->counts('activities'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Crear Componente')
                    ->mutateFormDataUsing(function (array $data, $livewire): array {
                        $data['program_id'] = $livewire->getOwnerRecord()->id;
                        return $data;
                    }),
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
}
