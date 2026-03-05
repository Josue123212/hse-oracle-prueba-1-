<?php

namespace App\Filament\Resources\Committees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use App\Models\ActivityExecution;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Model;

class CommitteesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombre')
                    ->label('Reunión')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tema_principal')
                    ->label('Tema')
                    ->searchable(),

                TextColumn::make('activity.frecuencia')
                    ->label('Frecuencia')
                    ->sortable()
                    ->badge(),

                TextColumn::make('activity.veces_al_anio')
                    ->label('Veces/Año')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('responsable.nombre')
                    ->label('Cargo Responsable')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('executions')
                    ->label('Gestionar Ejecuciones')
                    ->icon('heroicon-m-calendar-days')
                    ->color('info')
                    ->form(function ($record) {
                        $activity = $record->activity;
                        if (!$activity) {
                            return [
                                Placeholder::make('no_activity')
                                    ->content('No hay actividad asociada a este registro.')
                            ];
                        }
                        $executions = $activity->executions()->orderBy('fecha_programada')->get();

                        // Campos comunes para reutilizar
                        $getFields = function ($execution, $prefix = '') {
                            $fieldName = fn($name) => $prefix ? "{$prefix}.{$name}" : $name;
                            $getData = fn($key) => $execution->data[$key] ?? null;
                            
                            $fields = [
                                Hidden::make($fieldName('id'))
                                    ->default($execution->id),
                                    
                                DatePicker::make($fieldName('fecha_programada'))
                                    ->label('Fecha Programada')
                                    ->default($execution->fecha_programada)
                                    ->disabled()
                                    ->required(),
                                    
                                Select::make($fieldName('estado'))
                                    ->label('Estado')
                                    ->options(\App\Enums\ActivityState::class)
                                    ->default($execution->estado)
                                    ->required(),
                                    
                                DatePicker::make($fieldName('fecha_ejecucion_real'))
                                    ->label('Fecha Real')
                                    ->default($execution->fecha_ejecucion_real),
                                    
                                Textarea::make($fieldName('observacion'))
                                    ->label('Observaciones')
                                    ->default($execution->observacion)
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ];

                            // Campos dinámicos de Comités
                            $fields[] = Section::make('Detalles de la Reunión')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make($fieldName('data.asistentes_count'))
                                                ->label('Número de Asistentes')
                                                ->numeric()
                                                ->default($getData('asistentes_count')),
                                            TextInput::make($fieldName('data.acta_referencia'))
                                                ->label('Referencia de Acta')
                                                ->default($getData('acta_referencia')),
                                            Textarea::make($fieldName('data.compromisos'))
                                                ->label('Compromisos Adquiridos')
                                                ->default($getData('compromisos'))
                                                ->rows(2)
                                                ->columnSpanFull(),
                                        ]),
                                ]);

                            return $fields;
                        };

                        // Caso 1: Una sola ejecución (o ninguna)
                        if ($executions->count() <= 1) {
                            $execution = $executions->first();
                            if (!$execution) {
                                return [
                                    Placeholder::make('no_data')
                                        ->content('No hay ejecuciones programadas para esta actividad.')
                                ];
                            }
                            
                            return $getFields($execution, 'single_execution');
                        }

                        // Caso 2: Múltiples ejecuciones (Tabs/Burbujas)
                        $tabs = [];
                        foreach ($executions as $execution) {
                            $monthName = \Carbon\Carbon::parse($execution->fecha_programada)->locale('es')->translatedFormat('M');
                            $day = \Carbon\Carbon::parse($execution->fecha_programada)->format('d');
                            
                            $tabs[] = Tab::make("{$monthName} {$day}")
                                ->schema($getFields($execution, "executions.{$execution->id}"));
                        }

                        return [
                            Tabs::make('Ejecuciones')
                                ->tabs($tabs)
                                ->persistTabInQueryString('execution_tab')
                        ];
                    })
                    ->action(function (array $data) {
                        $saveExecution = function ($id, $item) {
                            $extraData = $item['data'] ?? [];

                            ActivityExecution::where('id', $id)->update([
                                'estado' => $item['estado'],
                                'fecha_ejecucion_real' => $item['fecha_ejecucion_real'],
                                'observacion' => $item['observacion'],
                                'data' => $extraData,
                            ]);
                        };

                        // Guardar Caso 1
                        if (isset($data['single_execution'])) {
                            $item = $data['single_execution'];
                            $saveExecution($item['id'], $item);
                        }
                        // Guardar Caso 2
                        elseif (isset($data['executions'])) {
                            foreach ($data['executions'] as $id => $item) {
                                $saveExecution($id, $item);
                            }
                        }
                        
                        Notification::make()
                            ->title('Ejecuciones actualizadas correctamente')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('Guardar Cambios'),

                ViewAction::make(),
                EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        $service = new \App\Services\ActivityService();
                        return $service->updateWithType($record, $data);
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
