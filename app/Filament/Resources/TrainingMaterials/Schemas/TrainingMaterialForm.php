<?php

namespace App\Filament\Resources\TrainingMaterials\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class TrainingMaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('training_id')
                    ->label('Capacitación')
                    ->relationship('training', 'titulo')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('nombre')
                    ->label('Nombre del material')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url_material')
                    ->label('URL del material')
                    ->maxLength(500),
            ]);
    }
}
