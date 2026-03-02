<?php

namespace App\Filament\Resources\Documentations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\Documentation;
use App\Models\Activity;
use App\Enums\ActivityState;
use Livewire\Livewire;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use App\Models\ActivityExecution;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Carbon\Carbon;

use Filament\Forms\Components\FileUpload;

class DocumentationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('titulo')
                    ->label('Documento')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('tipo_documento')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('version')
                    ->label('Versión')
                    ->searchable(),

                TextColumn::make('responsable.nombre')
                    ->label('Cargo Responsable')
                    ->sortable(),

                TextColumn::make('activity.frecuencia')
                    ->label('Frecuencia')
                    ->sortable()
                    ->badge(),
            ])
            ->actions([
                Action::make('executions')
                    ->label('Gestionar Ejecuciones')
                    ->icon('heroicon-m-calendar-days')
                    ->color('info')
                    ->fillForm(function ($record) {
                        if (!$record->activity) {
                            return [];
                        }

                        $data = [];
                        foreach ($record->activity->executions as $execution) {
                            $data["execution_{$execution->id}"] = [
                                'id' => $execution->id,
                                'fecha_programada' => $execution->fecha_programada->format('Y-m-d'),
                                'estado' => $execution->estado->value,
                                'fecha_ejecucion_real' => $execution->fecha_ejecucion_real?->format('Y-m-d'),
                                'observacion' => $execution->observacion,
                                'evidencia' => $execution->evidencia,
                                'data' => $execution->data ?? [],
                            ];
                        }
                        return $data;
                    })
                    ->form(function ($record) {
                        if (!$record->activity) {
                            return [
                                Placeholder::make('no_activity')
                                    ->label('Sin Actividad')
                                    ->content('Este registro no tiene una actividad asociada configurada.'),
                            ];
                        }

                        $executions = $record->activity->executions;
                        
                        // Definición de campos comunes
                        $getFields = function ($executionId) use ($executions) {
                            $execution = $executions->find($executionId);
                            $fieldName = fn($field) => "execution_{$executionId}.{$field}";
                            $getData = fn($key) => $execution->data[$key] ?? null;

                            $fields = [
                                Hidden::make($fieldName('id'))
                                    ->default($executionId),
                                
                                Section::make('Estado de Ejecución')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                DatePicker::make($fieldName('fecha_programada'))
                                                    ->label('Fecha Programada')
                                                    ->disabled()
                                                    ->default($execution->fecha_programada),
                                                
                                                Select::make($fieldName('estado'))
                                                    ->label('Estado')
                                                    ->options(ActivityState::class)
                                                    ->default($execution->estado)
                                                    ->required()
                                                    ->live(),

                                                DatePicker::make($fieldName('fecha_ejecucion_real'))
                                                    ->label('Fecha Real')
                                                    ->default($execution->fecha_ejecucion_real),
                                            ]),
                                        
                                        Textarea::make($fieldName('observacion'))
                                            ->label('Observaciones Generales')
                                            ->rows(2)
                                            ->default($execution->observacion)
                                            ->columnSpanFull(),

                                        FileUpload::make($fieldName('evidencia'))
                                            ->label('Evidencia / Archivo')
                                            ->directory('execution_evidence')
                                            ->downloadable()
                                            ->openable()
                                            ->default($execution->evidencia)
                                            ->columnSpanFull(),
                                    ]),
                            ];

                            // Campos dinámicos de Documentación
                            $fields[] = Section::make('Control de Documento')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make($fieldName('data.version'))
                                                ->label('Versión del Documento')
                                                ->default($getData('version')),
                                            
                                            DatePicker::make($fieldName('data.fecha_vencimiento'))
                                                ->label('Fecha de Vencimiento')
                                                ->default($getData('fecha_vencimiento')),

                                            Select::make($fieldName('data.estado_revision'))
                                                ->label('Estado de Revisión')
                                                ->options([
                                                    'borrador' => 'Borrador',
                                                    'revision' => 'En Revisión',
                                                    'aprobado' => 'Aprobado',
                                                    'obsoleto' => 'Obsoleto',
                                                ])
                                                ->default($getData('estado_revision')),

                                            TextInput::make($fieldName('data.ubicacion_fisica'))
                                                ->label('Ubicación Física/Digital')
                                                ->placeholder('URL o referencia de archivo')
                                                ->default($getData('ubicacion_fisica')),
                                        ]),
                                ]);

                            return $fields;
                        };

                        // Lógica de visualización (pestañas o directo)
                        if ($executions->count() > 1) {
                            return [
                                Tabs::make('Ejecuciones')
                                    ->tabs(
                                        $executions->map(function ($execution) use ($getFields) {
                                            Carbon::setLocale('es');
                                            $date = Carbon::parse($execution->fecha_programada);
                                            $label = $date->translatedFormat('F d, Y'); // Ej: Enero 15, 2024
                                            
                                            return Tabs\Tab::make($label)
                                                ->schema($getFields($execution->id));
                                        })->toArray()
                                    )
                                    ->persistTabInQueryString()
                                    ->activeTab(1),
                            ];
                        }

                        // Caso de una sola ejecución
                        if ($executions->count() === 1) {
                            return $getFields($executions->first()->id);
                        }

                        return [
                            Placeholder::make('no_executions')
                                ->label('Sin Ejecuciones')
                                ->content('No hay ejecuciones programadas para esta actividad.'),
                        ];
                    })
                    ->action(function (array $data, $record) {
                        $saveExecution = function ($executionData) {
                            $execution = ActivityExecution::find($executionData['id']);
                            if ($execution) {
                                $execution->update([
                                    'estado' => $executionData['estado'],
                                    'fecha_ejecucion_real' => $executionData['fecha_ejecucion_real'],
                                    'observacion' => $executionData['observacion'],
                                    'evidencia' => $executionData['evidencia'] ?? null,
                                    'data' => $executionData['data'] ?? [],
                                ]);
                            }
                        };

                        foreach ($data as $key => $value) {
                            if (str_starts_with($key, 'execution_')) {
                                $saveExecution($value);
                            }
                        }

                        Notification::make()
                            ->title('Ejecuciones actualizadas correctamente')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('4xl'),


                ViewAction::make(),
                EditAction::make()
                    ->after(function (Documentation $record) {
                        // Force update activity if exists to ensure sync
                        if ($record->activity_id) {
                            $activity = Activity::find($record->activity_id);
                            if ($activity) {
                                $activity->update([
                                    'fecha_inicio' => $record->fecha_programada,
                                    'fecha_fin' => $record->fecha_aprobacion ?? $record->fecha_programada,
                                    'nombre' => $record->titulo,
                                ]);
                            }
                        }
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
