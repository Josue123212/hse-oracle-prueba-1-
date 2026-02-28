<?php

namespace App\Filament\Resources\Trainings\Tables;

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

class TrainingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.nombre')
                    ->label('Programa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tema')
                    ->label('Tema')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('activity.frecuencia')
                    ->label('Frecuencia')
                    ->sortable()
                    ->badge(),

                TextColumn::make('activity.veces_al_anio')
                    ->label('Veces/Año')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('responsable.name')
                    ->label('Responsable'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('executions')
                    ->label('Gestionar Ejecuciones')
                    ->icon('heroicon-m-calendar-days')
                    ->color('info')
                    ->fillForm(fn ($record): array => [
                        'executions_data' => ($record->activity ? $record->activity->executions : collect())->map(fn ($execution) => [
                            'id' => $execution->id,
                            'fecha_programada' => $execution->fecha_programada->format('Y-m-d'),
                            'estado' => $execution->estado,
                            'fecha_ejecucion_real' => $execution->fecha_ejecucion_real?->format('Y-m-d'),
                            'observacion' => $execution->observacion,
                        ])->toArray(),
                    ])
                    ->form([
                        Repeater::make('executions_data')
                            ->label('Cronograma de Ejecuciones')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(3)
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('fecha_programada')
                                    ->label('Fecha Programada')
                                    ->disabled()
                                    ->required(),
                                Select::make('estado')
                                    ->options([
                                        'pendiente' => 'Pendiente',
                                        'ejecutado' => 'Ejecutado',
                                        'no_cumplio' => 'No Cumplió',
                                        'vencido' => 'Vencido',
                                    ])
                                    ->required(),
                                DatePicker::make('fecha_ejecucion_real')
                                    ->label('Fecha Real'),
                                Textarea::make('observacion')
                                    ->label('Observaciones')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->action(function (array $data): void {
                        foreach ($data['executions_data'] as $item) {
                            ActivityExecution::where('id', $item['id'])->update([
                                'estado' => $item['estado'],
                                'fecha_ejecucion_real' => $item['fecha_ejecucion_real'],
                                'observacion' => $item['observacion'],
                            ]);
                        }
                        
                        Notification::make()
                            ->title('Ejecuciones actualizadas correctamente')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('Guardar Cambios'),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
