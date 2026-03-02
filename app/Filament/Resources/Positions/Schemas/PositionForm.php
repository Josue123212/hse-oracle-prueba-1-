<?php

namespace App\Filament\Resources\Positions\Schemas;

use Filament\Schemas\Schema;

class PositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('nombre')
                    ->label('Nombre del Cargo')
                    ->required()
                    ->maxLength(100),

                \Filament\Forms\Components\Select::make('position_type_id')
                    ->label('Tipo de Cargo')
                    ->relationship('positionType', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm(fn (\Filament\Schemas\Schema $schema) => \App\Filament\Resources\PositionTypes\Schemas\PositionTypeForm::configure($schema)->getComponents()),

                \Filament\Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->columnSpanFull(),

                \Filament\Schemas\Components\Section::make('Firma')
                    ->schema([
                        \Filament\Forms\Components\Radio::make('tipo_firma')
                            ->label('Método de Firma')
                            ->options([
                                'digital' => 'Dibujar en pantalla',
                                'archivo' => 'Subir imagen',
                            ])
                            ->default('digital')
                            ->inline()
                            ->live()
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->firma) {
                                    if (str_starts_with($record->firma, 'data:image')) {
                                        $component->state('digital');
                                    } else {
                                        $component->state('archivo');
                                    }
                                }
                            }),

                        \Filament\Forms\Components\ViewField::make('firma_digital')
                            ->label('Firma Digital')
                            ->view('filament.forms.components.signature-pad')
                            ->helperText('Use el ratón o dedo para firmar en el recuadro.')
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('tipo_firma') === 'digital')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->firma && str_starts_with($record->firma, 'data:image')) {
                                    $component->state($record->firma);
                                }
                            }),

                        \Filament\Forms\Components\FileUpload::make('firma')
                            ->label('Subir Imagen de Firma')
                            ->image()
                            ->directory('firmas')
                            ->helperText('Suba una imagen (PNG, JPG, SVG) de su firma.')
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('tipo_firma') === 'archivo'),
                    ])
                    ->collapsible(),
            ]);
    }
}
