<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
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

abstract class BaseEventualActivityWidget extends BaseWidget
{
    use HandlesActivityExecution;

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '30s';

    protected string $view = 'filament.widgets.base-activity-widget';

    abstract protected function getActivityType(): string;

    abstract protected function getHeadingTitle(): string;

    abstract protected function getActivityLabel(): string;

    abstract protected function getIniciaFormDetails(): array;

    #[On('activity-executed')]
    public function refresh(): void
    {
    }

    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query;
    }

    public function table(Table $table): Table
    {
        $query = Activity::query()
            ->where('tipo', $this->getActivityType())
            ->where('frecuencia', 'eventual');

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
                        Section::make('Detalles de la Actividad')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('component.program.nombre')
                                            ->label('Programa')
                                            ->placeholder('N/A'),
                                        TextEntry::make('nombre')
                                            ->label('Actividad'),
                                        TextEntry::make('tipo')
                                            ->label('Tipo')
                                            ->formatStateUsing(fn ($state) => ucfirst($state)),
                                        TextEntry::make('frecuencia')
                                            ->label('Frecuencia')
                                            ->formatStateUsing(fn ($state) => ucfirst($state)),
                                    ]),
                                TextEntry::make('descripcion')
                                    ->label('Descripción')
                                    ->columnSpanFull(),
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('responsable.nombre')
                                            ->label('Responsable')
                                            ->placeholder('Sin asignar'),
                                        TextEntry::make('location.nombre')
                                            ->label('Sede')
                                            ->placeholder('Sin asignar'),
                                    ]),
                            ]),
                    ]),
                $this->getIniciarAction(),
            ])
            ->paginated(false);
    }

    protected function getTableColumns(): array
    {
        return [
            Stack::make([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Actividad')
                    ->weight(FontWeight::Bold)
                    ->size('lg')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->color('gray'),

                Split::make([
                        Tables\Columns\TextColumn::make('frecuencia')
                        ->badge()
                        ->color('warning'),
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
            ->label(fn (Activity $record) => $this->hasExecutedToday($record) ? 'Completado Hoy' : 'Iniciar')
            ->icon(fn (Activity $record) => $this->hasExecutedToday($record) ? 'heroicon-o-check-circle' : 'heroicon-o-play')
            ->color(fn (Activity $record) => $this->hasExecutedToday($record) ? 'gray' : 'success')
            ->disabled(fn (Activity $record) => $this->hasExecutedToday($record))
            ->button()
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
                    ->columnSpanFull(),

                \Filament\Schemas\Components\Section::make('Detalles de ' . $this->getActivityLabel())
                    ->schema($this->getIniciaFormDetails()),
            ])
            ->modalHeading('Ejecutar ' . $this->getActivityLabel() . ' Eventual')
            ->modalSubmitActionLabel('Guardar')
            ->action(function (Activity $record, array $data) {
                $this->processExecution($record, $data);
            });
    }

    protected function hasExecutedToday(Activity $activity): bool
    {
        return $activity->executions()
            ->whereDate('created_at', now())
            ->exists();
    }

    protected function processExecution(Activity $record, array $data): void
    {
        \Illuminate\Support\Facades\Log::info('--- INICIO GUARDADO ' . strtoupper($this->getActivityLabel()) . ' EVENTUAL ---');
        
        $evidenciaLocalPaths = $data['evidencia'] ?? null;
        $finalDrivePaths = $this->moveEvidenceFilesToDrive($record, $evidenciaLocalPaths);
        $finalDrivePath = $finalDrivePaths[0] ?? null;

        ActivityExecution::create([
            'activity_id' => $record->id,
            'observacion' => $data['observacion'] ?? null,
            'evidencia' => $finalDrivePath ? [$finalDrivePath] : null,
            'estado' => \App\Enums\ActivityState::EJECUTADO,
            'fecha_programada' => now(),
            'fecha_ejecucion_real' => now(),
            'data' => $data['data'] ?? [],
        ]);
        
        Notification::make()
            ->title($this->getActivityLabel() . ' Eventual Registrada')
            ->success()
            ->send();
        \Illuminate\Support\Facades\Log::info('--- FIN GUARDADO ' . strtoupper($this->getActivityLabel()) . ' EVENTUAL ---');
        $this->dispatch('activity-executed');
    }
}
