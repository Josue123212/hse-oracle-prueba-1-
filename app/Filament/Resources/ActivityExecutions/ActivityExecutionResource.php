<?php

namespace App\Filament\Resources\ActivityExecutions;

use App\Filament\Resources\ActivityExecutions\Pages\ManageActivityExecutions;
use App\Models\ActivityExecution;
use App\Enums\ActivityState;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Program;
use UnitEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;

class ActivityExecutionResource extends Resource
{
    protected static ?string $model = ActivityExecution::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Programas';

    protected static ?string $navigationLabel = 'Ejecuciones';

    protected static ?string $modelLabel = 'Ejecución';

    protected static ?string $pluralModelLabel = 'Ejecuciones';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('activity_id')
                                    ->relationship('activity', 'nombre', modifyQueryUsing: fn (Builder $query, $operation) => 
                                        $operation === 'create' ? $query->where('frecuencia', 'eventual') : $query
                                    )
                                    ->label('Actividad')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->disabledOn('edit'),
                                DatePicker::make('fecha_programada')
                                    ->label('Fecha Programada')
                                    ->required(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->frecuencia !== 'eventual')
                                    ->disabledOn('edit'),
                                DatePicker::make('fecha_ejecucion_real')
                                    ->label('Fecha Ejecución Real')
                                    ->default(now())
                                    ->required(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->frecuencia === 'eventual'),
                                Select::make('estado')
                                    ->label('Estado')
                                    ->options(ActivityState::class)
                                    ->required(),
                            ]),
                        Textarea::make('observacion')
                            ->label('Observación')
                            ->rows(2)
                            ->columnSpanFull(),
                        Section::make('Evidencias')
                            ->schema([
                                Placeholder::make('evidencias_grid')
                                    ->label('Archivos Existentes')
                                    ->content(function ($record, $operation) {
                                        if (!$record || empty($record->evidencia)) return 'No hay evidencias.';
                                        
                                        $files = [];
                                        $evidences = $record->evidencia;
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
                                        
                                        // View mode: Download icon. Edit mode: Preview (Eye) icon.
                                        $mode = $operation === 'view' ? 'view' : 'edit';
                                        
                                        return view('filament.components.evidence-grid', [
                                            'files' => $files, 
                                            'mode' => $mode,
                                            'recordId' => $record->id
                                        ]);
                                    })
                                    ->columnSpanFull(),

                                FileUpload::make('new_evidencia')
                                    ->label('Agregar Nuevas Evidencias')
                                    ->disk('public')
                                    ->directory('temp-uploads')
                                    ->multiple()
                                    ->preserveFilenames()
                                    ->columnSpanFull()
                                    ->visible(fn ($operation) => $operation !== 'view'),
                            ])
                            ->columnSpanFull(),
                    ]),

                // Secciones dinámicas según el tipo de actividad
                Section::make('Detalles de Auditoría')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('data.auditor_id')
                                    ->label('Supervisor')
                                    ->options(\App\Models\Supervisor::pluck('nombre', 'id'))
                                    ->searchable()
                                    ->preload(),
                                Textarea::make('data.hallazgos')
                                    ->label('Hallazgos'),
                            ]),
                    ])
                    ->visible(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->tipo === 'auditoria'),

                Section::make('Detalles de Inspección')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('data.location_id')
                                    ->label('Ubicación')
                                    ->options(\App\Models\Location::pluck('nombre', 'id'))
                                    ->searchable()
                                    ->preload(),
                                Textarea::make('data.observaciones')
                                    ->label('Observaciones Adicionales'),
                            ]),
                    ])
                    ->visible(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->tipo === 'inspeccion'),

                Section::make('Detalles de Capacitación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('data.tema')
                                    ->label('Tema Específico'),
                                TimePicker::make('data.hora_inicio')
                                    ->label('Hora de Inicio'),
                                TextInput::make('data.duracion_horas')
                                    ->label('Duración Real (Horas)')
                                    ->numeric(),
                                TextInput::make('data.asistentes_reales')
                                    ->label('Asistentes Reales')
                                    ->numeric(),
                            ]),
                    ])
                    ->visible(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->tipo === 'capacitacion'),

                Section::make('Detalles de Simulacro')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('data.escenario')
                                    ->label('Escenario del Simulacro'),
                                TextInput::make('data.participantes_count')
                                    ->label('Participantes Reales')
                                    ->numeric(),
                            ]),
                    ])
                    ->visible(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->tipo === 'simulacro'),

                Section::make('Detalles de Incidente')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('data.lugar')
                                    ->label('Lugar del Incidente'),
                                Select::make('data.severidad')
                                    ->label('Severidad')
                                    ->options([
                                        'leve' => 'Leve',
                                        'moderado' => 'Moderado',
                                        'grave' => 'Grave',
                                        'critico' => 'Crítico',
                                    ]),
                            ]),
                    ])
                    ->visible(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->tipo === 'incidente'),

                Section::make('Detalles de Comité')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('data.tema_principal')
                                    ->label('Tema Tratado'),
                            ]),
                    ])
                    ->visible(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->tipo === 'comite'),

                Section::make('Detalles de Documentación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('data.tipo_documento')
                                    ->label('Tipo de Documento')
                                    ->options([
                                        'procedimiento' => 'Procedimiento',
                                        'formato' => 'Formato',
                                        'politica' => 'Política',
                                        'manual' => 'Manual',
                                        'otro' => 'Otro',
                                    ]),
                                TextInput::make('data.version')
                                    ->label('Versión Generada'),
                            ]),
                    ])
                    ->visible(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->tipo === 'documentacion'),

                Section::make('Detalles de Promoción')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('data.publico_objetivo')
                                    ->label('Público Alcanzado'),
                                TextInput::make('data.material_entregado')
                                    ->label('Material Entregado'),
                                TextInput::make('data.participantes_estimados')
                                    ->label('Participantes Totales')
                                    ->numeric(),
                            ]),
                    ])
                    ->visible(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->tipo === 'promocion'),

                Section::make('Detalles de Control Operacional')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('data.parametro')
                                    ->label('Parámetro Controlado'),
                                TextInput::make('data.valor_obtenido')
                                    ->label('Valor Obtenido'),
                            ]),
                    ])
                    ->visible(fn (Get $get) => \App\Models\Activity::find($get('activity_id'))?->tipo === 'control_operacional'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('activity.nombre')
                    ->label('Actividad')
                    ->collapsible(),
            ])
            ->defaultGroup('activity.nombre')
            ->columns([
                TextColumn::make('activity.nombre')
                    ->label('Actividad')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fecha_programada')
                    ->label('Fecha Programada / Ejecución')
                    ->date('d/m/Y')
                    ->default(fn (ActivityExecution $record) => $record->fecha_ejecucion_real?->format('d/m/Y'))
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('observacion')
                    ->label('Observación')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
            ])
            ->filters([
                SelectFilter::make('program')
                    ->label('Programa')
                    ->searchable()
                    ->options(fn () => Program::pluck('nombre', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $programId) => $query->whereHas('activity', fn (Builder $query) => $query->where('program_id', $programId))
                        );
                    }),
                SelectFilter::make('tipo')
                    ->label('Tipo de Actividad')
                    ->options([
                        'general' => 'General',
                        'auditoria' => 'Auditoría',
                        'inspeccion' => 'Inspección',
                        'capacitacion' => 'Capacitación',
                        'simulacro' => 'Simulacro',
                        'incidente' => 'Incidente',
                        'comite' => 'Comité',
                        'documentacion' => 'Documentación',
                        'promocion' => 'Promoción',
                        'control_operacional' => 'Control Operacional',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $tipo) => $query->whereHas('activity', fn (Builder $query) => $query->where('tipo', $tipo))
                        );
                    }),
                SelectFilter::make('estado')
                    ->options(ActivityState::class),
            ])
            ->actions([
                Action::make('ver_evidencias')
                    ->icon('heroicon-o-folder-open')
                    ->label('Evidencias')
                    ->color('info')
                    ->modalContent(function ($record) {
                        $files = [];
                        $evidences = $record->evidencia;
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
                        return view('filament.components.evidence-grid', ['files' => $files, 'mode' => 'edit']);
                    })
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn () => Action::make('cerrar')->label('Cerrar')->close())
                    ->visible(fn ($record) => !empty($record->evidencia)),
                ViewAction::make(),
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data, ActivityExecution $record): array {
                        $oldEvidences = $record->evidencia;
                        if (is_string($oldEvidences)) {
                            $decoded = json_decode($oldEvidences, true);
                            $oldEvidences = is_array($decoded) ? $decoded : [$oldEvidences];
                        }
                        $oldEvidences = $oldEvidences ?? [];
                        
                        // 1. Handle Existing Files (Deletion)
                        $keptEvidences = [];
                        if (isset($data['existing_evidences'])) {
                            foreach ($data['existing_evidences'] as $item) {
                                if (isset($item['path'])) {
                                    $keptEvidences[] = $item['path'];
                                }
                            }
                        } else {
                            // If the repeater is not in the form, we assume we keep all existing evidences.
                            // This prevents accidental deletion when using the ViewField/Grid.
                            $keptEvidences = $oldEvidences;
                        }
                        
                        // Find deleted files
                        $deletedEvidences = array_diff($oldEvidences, $keptEvidences);
                        foreach ($deletedEvidences as $path) {
                            try {
                                if (Storage::disk('google')->exists($path)) {
                                    Storage::disk('google')->delete($path);
                                }
                            } catch (\Exception $e) {
                                // Log error or ignore
                            }
                        }
                        
                        // 2. Handle New Files (Upload & Move)
                        $newEvidences = $data['new_evidencia'] ?? [];
                        $finalNewPaths = [];
                        
                        if (!empty($newEvidences)) {
                            $targetDir = \App\Services\DrivePathGenerator::generate($record);
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
                        
                        // 3. Merge and Set
                        $data['evidencia'] = array_merge($keptEvidences, $finalNewPaths);
                        
                        // Cleanup
                        unset($data['existing_evidences']);
                        unset($data['new_evidencia']);
                        
                        return $data;
                    }),
                DeleteAction::make()
                    ->visible(fn (ActivityExecution $record) => $record->fecha_programada === null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            // Filter out scheduled records
                            $recordsToDelete = $records->reject(fn ($record) => $record->fecha_programada !== null);
                            
                            $recordsToDelete->each->delete();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageActivityExecutions::route('/'),
        ];
    }
}
