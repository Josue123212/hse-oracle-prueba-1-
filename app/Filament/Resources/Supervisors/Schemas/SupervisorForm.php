<?php

namespace App\Filament\Resources\Supervisors\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;

class SupervisorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')
                ->label('Nombre Completo del Supervisor')
                ->required()
                ->maxLength(150),

            // Sección de Firma
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
                        ->required()
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
                        ->required()
                        ->directory('firmas')
                        ->helperText('Suba una imagen (PNG, JPG, SVG) de su firma.')
                        ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('tipo_firma') === 'archivo'),
                ])
                ->collapsible(),
        ]);
    }
}