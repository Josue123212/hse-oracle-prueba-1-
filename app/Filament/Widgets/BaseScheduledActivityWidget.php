<?php

namespace App\Filament\Widgets;

use App\Models\ActivityExecution;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Livewire\Attributes\On;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use App\Filament\Traits\HandlesActivityExecution;

abstract class BaseScheduledActivityWidget extends BaseWidget
{
    use \App\Filament\Traits\HasEvidencePreview;
    use HandlesActivityExecution;

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '30s';

    protected string $view = 'filament.widgets.base-activity-widget';

    abstract protected function getActivityType(): string;

    abstract protected function getHeadingTitle(): string;

    abstract protected function getActivityLabel(): string;

    abstract protected function getIniciaFormDetails(): array;

    #[On('activity-updated')]
    public function refresh(): void
    {
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query;
    }

    public function table(Table $table): Table
    {
        $query = ActivityExecution::query()
            ->with('activity')
            ->whereHas('activity', fn ($query) => $query->where('tipo', $this->getActivityType()))
            ->whereDate('fecha_programada', now())
            ->whereIn('estado', [
                \App\Enums\ActivityState::PROGRAMADO,
                \App\Enums\ActivityState::EN_PROCESO,
                \App\Enums\ActivityState::EJECUTADO
            ]);

        return $table
            ->query($this->modifyQuery($query))
            ->heading(null)
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->recordAction('view')
            ->columns($this->getTableColumns())
            ->actions([
                ViewAction::make()
                    ->infolist([
                        Section::make('Información General')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('activity.component.program.nombre')
                                            ->label('Programa')
                                            ->placeholder('N/A'),
                                        TextEntry::make('activity.nombre')
                                            ->label($this->getActivityLabel()),
                                        TextEntry::make('activity.tipo')
                                            ->label('Tipo')
                                            ->formatStateUsing(fn ($state) => ucfirst($state)),
                                        TextEntry::make('activity.descripcion')
                                            ->label('Descripción')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Section::make('Detalles de Ejecución')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('fecha_programada')
                                            ->label('Fecha Programada')
                                            ->date(),
                                        TextEntry::make('fecha_ejecucion_real')
                                            ->label('Fecha Ejecución')
                                            ->date()
                                            ->placeholder('Pendiente'),
                                        TextEntry::make('estado')
                                            ->label('Estado')
                                            ->badge()
                                            ->color(fn ($state) => match ($state) {
                                                'programado' => 'gray',
                                                'en_proceso' => 'warning',
                                                'ejecutado' => 'success',
                                                default => 'gray',
                                            }),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('activity.responsable.nombre')
                                            ->label('Responsable')
                                            ->placeholder('Sin asignar'),
                                        TextEntry::make('activity.location.nombre')
                                            ->label('Sede')
                                            ->placeholder('Sin asignar'),
                                    ]),
                            ]),
                        Section::make('Evidencias y Observaciones')
                            ->schema([
                                TextEntry::make('observacion')
                                    ->label('Observaciones')
                                    ->placeholder('Sin observaciones'),
                            ]),
                    ]),
                Action::make('ver_evidencias')
                    ->icon('heroicon-o-folder-open')
                    ->label('Evidencias')
                    ->color('info')
                    ->modalContent(fn (ActivityExecution $record) => view('filament.components.evidence-grid', [
                        'files' => $this->getEvidenceFiles($record),
                        'mode' => 'view',
                    ]))
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn () => Action::make('cerrar')->label('Cerrar')->close())
                    ->visible(fn (ActivityExecution $record) => !empty($record->evidencia)),
                
                $this->getIniciarAction(),
            ])
            ->paginated(false);
    }

    protected function getTableColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('activity.nombre')
                    ->label($this->getActivityLabel())
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('activity.descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->color('gray'),

                Split::make([
                    Tables\Columns\TextColumn::make('fecha_programada')
                        ->date('H:i')
                        ->icon('heroicon-o-clock')
                        ->color('gray'),
                    
                    Tables\Columns\TextColumn::make('estado')
                        ->badge(),
                ]),
            ])->space(3),
        ];
    }

    protected function getObservacionLabel(): string
    {
        return 'Observaciones';
    }

    protected function getEvidenciaLabel(): string
    {
        return 'Evidencia (Archivo)';
    }

    protected function getIniciarAction(): Action
    {
        return Action::make('iniciar')
            ->label(fn (ActivityExecution $record) => match ($record->estado) {
                \App\Enums\ActivityState::EJECUTADO => 'Completado',
                \App\Enums\ActivityState::EN_PROCESO => 'Continuar',
                default => 'Iniciar',
            })
            ->icon(fn (ActivityExecution $record) => match ($record->estado) {
                \App\Enums\ActivityState::EJECUTADO => 'heroicon-o-check-circle',
                default => 'heroicon-o-play',
            })
            ->color(fn (ActivityExecution $record) => match ($record->estado) {
                \App\Enums\ActivityState::EJECUTADO => 'gray',
                default => 'success',
            })
            ->disabled(fn (ActivityExecution $record) => $record->estado === \App\Enums\ActivityState::EJECUTADO)
            ->button()
            ->mountUsing(function (ActivityExecution $record) {
                if ($record->estado === \App\Enums\ActivityState::PROGRAMADO) {
                    $record->update(['estado' => \App\Enums\ActivityState::EN_PROCESO]);
                }
            })
            ->form([
                \Filament\Forms\Components\Textarea::make('observacion')
                    ->label($this->getObservacionLabel())
                    ->rows(3)
                    ->columnSpanFull(),
                \Filament\Forms\Components\FileUpload::make('evidencia')
                    ->label($this->getEvidenciaLabel())
                    ->disk('public')
                    ->directory('temp-uploads')
                    ->visibility('private')
                    ->preserveFilenames()
                    ->multiple()
                    ->storeFileNamesIn('data->file_names')
                    ->columnSpanFull()
                    ->required(),

                \Filament\Schemas\Components\Section::make('Detalles de ' . $this->getActivityLabel())
                    ->schema($this->getIniciaFormDetails()),
            ])
            ->modalHeading('Ejecutar ' . $this->getActivityLabel())
            ->modalSubmitActionLabel('Guardar')
            ->action(function (ActivityExecution $record, array $data) {
                $this->processExecution($record, $data);
            });
    }

    protected function processExecution(ActivityExecution $record, array $data): void
    {
         \Illuminate\Support\Facades\Log::info('Inicio de acción guardar ' . $this->getActivityLabel(), ['record_id' => $record->id, 'data' => $data]);
                        
        $evidenciaLocalPaths = $data['evidencia'] ?? [];
        $finalDrivePaths = $this->moveEvidenceFilesToDrive($record, $evidenciaLocalPaths);
        
        $record->update([
            'estado' => \App\Enums\ActivityState::EJECUTADO,
            'fecha_ejecucion_real' => now(),
            'observacion' => $data['observacion'] ?? null,
            'evidencia' => $finalDrivePaths,
            'data' => array_merge($record->data ?? [], $data['data'] ?? []),
        ]);
        
        Notification::make()
            ->title($this->getActivityLabel() . ' Ejecutada')
            ->success()
            ->send();
    }
}
