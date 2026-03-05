<?php

namespace App\Filament\Resources\Inspections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
use App\Models\Location;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Model;

class InspectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('activity.nombre')
                    ->label('Actividad')
                    ->searchable()
                    ->sortable(),

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
                    ->searchable(),

                TextColumn::make('resultado')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'aprobado' => 'success',
                        'rechazado' => 'danger',
                        'pendiente' => 'warning',
                        default => 'gray',
                    }),
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
                                        
                                    Placeholder::make($fieldName('evidencias_grid'))
                                        ->label('Evidencias Existentes')
                                        ->content(function () use ($execution) {
                                            $files = [];
                                            $evidences = $execution->evidencia;
                                            
                                            if (is_string($evidences)) {
                                                $decoded = json_decode($evidences, true);
                                                $evidences = is_array($decoded) ? $decoded : [$evidences];
                                            }

                                            if (is_array($evidences)) {
                                                foreach ($evidences as $path) {
                                                    try {
                                                        $disk = Storage::disk('google');
                                                        $files[] = [
                                                            'path' => $path,
                                                            'name' => basename($path),
                                                            'mime' => $disk->mimeType($path) ?? 'application/octet-stream',
                                                            'size' => $disk->size($path) ?? 0,
                                                        ];
                                                    } catch (\Exception $e) {
                                                         $files[] = [
                                                            'path' => $path,
                                                            'name' => basename($path),
                                                            'mime' => 'application/octet-stream',
                                                            'size' => 0,
                                                        ];
                                                    }
                                                }
                                            }
                                            
                                            return view('filament.components.evidence-grid', [
                                                'files' => $files, 
                                                'mode' => 'edit',
                                                'recordId' => $execution->id
                                            ]);
                                        })
                                        ->columnSpanFull(),

                                    \Filament\Forms\Components\FileUpload::make($fieldName('new_evidencia'))
                                        ->label('Agregar Nuevas Evidencias')
                                        ->disk('public')
                                        ->directory('temp-uploads')
                                        ->multiple()
                                        ->preserveFilenames()
                                        ->columnSpanFull(),
                                ];

                            // Campos dinámicos de Inspección
                            $fields[] = Section::make('Detalles de Inspección')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make($fieldName('data.location_id'))
                                                ->label('Ubicación')
                                                ->options(Location::pluck('nombre', 'id'))
                                                ->default($getData('location_id'))
                                                ->searchable()
                                                ->preload(),
                                            Textarea::make($fieldName('data.observaciones'))
                                                ->label('Observaciones Adicionales')
                                                ->default($getData('observaciones')),
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

                            $execution = ActivityExecution::find($id);
                            if (!$execution) return;

                            $oldEvidences = $execution->evidencia;
                            if (is_string($oldEvidences)) {
                                $decoded = json_decode($oldEvidences, true);
                                $oldEvidences = is_array($decoded) ? $decoded : [$oldEvidences];
                            }
                            $oldEvidences = $oldEvidences ?? [];
                            
                            // Procesar nuevos archivos
                            $newEvidences = $item['new_evidencia'] ?? [];
                            $finalNewPaths = [];

                            if (!empty($newEvidences)) {
                                $targetDir = \App\Services\DrivePathGenerator::generate($execution);
                                if (!Storage::disk('google')->exists($targetDir)) {
                                     Storage::disk('google')->makeDirectory($targetDir);
                                }

                                foreach ($newEvidences as $tempPath) {
                                    if (Storage::disk('public')->exists($tempPath)) {
                                        $fileName = basename($tempPath);
                                        $targetPath = trim($targetDir, '/') . '/' . $fileName;
                                        
                                        Storage::disk('google')->put($targetPath, Storage::disk('public')->get($tempPath));
                                        if (Storage::disk('google')->exists($targetPath)) {
                                            $finalNewPaths[] = $targetPath;
                                            Storage::disk('public')->delete($tempPath);
                                        }
                                    }
                                }
                            }
                            
                            $finalEvidences = array_merge($oldEvidences, $finalNewPaths);

                            $execution->update([
                                'estado' => $item['estado'],
                                'fecha_ejecucion_real' => $item['fecha_ejecucion_real'],
                                'observacion' => $item['observacion'],
                                'evidencia' => $finalEvidences,
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
