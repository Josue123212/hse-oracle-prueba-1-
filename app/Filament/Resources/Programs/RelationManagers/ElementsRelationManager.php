<?php

namespace App\Filament\Resources\Programs\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

// IMPORTANTE: Importamos tus esquemas personalizados
use App\Filament\Resources\ElementTypes\Schemas\ElementTypeForm;
use App\Filament\Resources\SpecificObjectives\Schemas\SpecificObjectiveForm;
use App\Filament\Resources\SubElements\Schemas\SubElementForm;
use App\Filament\Resources\Activities\Schemas\ActivityForm;

class ElementsRelationManager extends RelationManager
{
    protected static string $relationship = 'elements';

    public function form(Schema $schema): Schema
{
    return $schema
        ->components([
            Select::make('element_type_id')
                ->relationship('elementType', 'nombre')
                ->preload()
                ->searchable()
                ->required()
                ->key('element_type_select')
                ->createOptionForm(fn (Schema $schema) => ElementTypeForm::configure($schema)->getComponents()), 

            Select::make('specific_objective_id')
                ->relationship('specificObjective', 'nombre')
                ->preload()
                ->searchable()
                ->key('specific_objective_select')
                ->createOptionForm(fn (Schema $schema) => SpecificObjectiveForm::configure($schema)->getComponents()),

            Select::make('sub_element_id')
                ->relationship('subElement', 'nombre')
                ->preload()
                ->searchable()
                ->key('sub_element_select')
                ->createOptionForm(fn (Schema $schema) => SubElementForm::configure($schema)->getComponents()),

            Select::make('activity_id')
                ->relationship('activity', 'nombre')
                ->preload()
                ->searchable()
                ->key('activity_select')
                ->createOptionForm(fn (Schema $schema) => ActivityForm::configure($schema)->getComponents()),

            TextInput::make('nombre')
                ->required(),

            Textarea::make('descripcion')
                ->columnSpanFull(),

            TextInput::make('orden')
                ->required()
                ->numeric()
                ->default(0),
        ]);
}

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('elementType.nombre')->label('Element type'),
                TextEntry::make('specificObjective.nombre')->label('Specific objective')->placeholder('-'),
                TextEntry::make('subElement.nombre')->label('Sub element')->placeholder('-'),
                TextEntry::make('activity.nombre')->label('Activity')->placeholder('-'),
                TextEntry::make('nombre'),
                TextEntry::make('descripcion')->placeholder('-')->columnSpanFull(),
                TextEntry::make('orden')->numeric(),
                TextEntry::make('created_at')->dateTime()->placeholder('-'),
                TextEntry::make('updated_at')->dateTime()->placeholder('-'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('elementType.nombre')->label('Type')->searchable(),
                TextColumn::make('specificObjective.nombre')->label('Objective')->searchable(),
                TextColumn::make('subElement.nombre')->label('Sub Element')->searchable(),
                TextColumn::make('activity.nombre')->label('Activity')->searchable(),
                TextColumn::make('nombre')->searchable(),
                TextColumn::make('orden')->numeric()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Crear Elemento'),
                AssociateAction::make()
                    ->label('Asociar'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}