<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Panel de Control';
    protected static ?string $title = 'Panel de Control';

    public function mount(): void
    {
        $user = auth()->user();
        
        // 1. Critical: Overdue Activities
        $overdueCount = \App\Models\ActivityExecution::whereDate('fecha_programada', '<', now()->startOfDay())
            ->whereIn('estado', [\App\Enums\ActivityState::PROGRAMADO, \App\Enums\ActivityState::EN_PROCESO])
            ->count();

        if ($overdueCount > 0) {
            $title = 'Atención: Ejecuciones Vencidas';
            
            // Check for existing unread notification to avoid spam
            $hasNotification = $user->unreadNotifications
                ->contains(fn ($notification) => ($notification->data['title'] ?? '') === $title);

            if (! $hasNotification) {
                // Persistent Notification (Bell)
                Notification::make()
                    ->title($title)
                    ->body("Tienes {$overdueCount} actividades vencidas que requieren atención inmediata.")
                    ->danger()
                    ->persistent()
                    ->actions([
                        Action::make('view')
                            ->label('Revisar')
                            ->button()
                            ->close(),
                    ])
                    ->sendToDatabase($user);
                
                // Immediate Feedback (Toast)
                Notification::make()
                    ->title($title)
                    ->body("Tienes {$overdueCount} actividades vencidas.")
                    ->danger()
                    ->send();
            }
        }

        // 2. Medium: Today's Activities
        $todayCount = \App\Models\ActivityExecution::whereDate('fecha_programada', now())
            ->whereIn('estado', [\App\Enums\ActivityState::PROGRAMADO, \App\Enums\ActivityState::EN_PROCESO])
            ->count();

        if ($todayCount > 0) {
            $title = 'Actividades para Hoy';
            $hasNotification = $user->unreadNotifications
                ->contains(fn ($notification) => ($notification->data['title'] ?? '') === $title);
                
            if (! $hasNotification) {
                Notification::make()
                    ->title($title)
                    ->body("Tienes {$todayCount} actividades programadas para hoy.")
                    ->warning()
                    ->sendToDatabase($user);
            }
        }

        // 3. Low: Upcoming Activities (Next 3 days)
        // Visible only if no urgent matters (Overdue or Today)
        if ($overdueCount === 0 && $todayCount === 0) {
            $upcomingCount = \App\Models\ActivityExecution::whereDate('fecha_programada', '>', now())
                ->whereDate('fecha_programada', '<=', now()->addDays(3))
                ->whereIn('estado', [\App\Enums\ActivityState::PROGRAMADO])
                ->count();

            if ($upcomingCount > 0) {
                $title = 'Próximas Actividades';
                $hasNotification = $user->unreadNotifications
                    ->contains(fn ($notification) => ($notification->data['title'] ?? '') === $title);
                    
                if (! $hasNotification) {
                    Notification::make()
                        ->title($title)
                        ->body("Tienes {$upcomingCount} actividades programadas para los próximos 3 días.")
                        ->info()
                        ->sendToDatabase($user);
                }
            }
        }
    }

    protected function getHeaderActions(): array
    {
        $programsCount = \App\Models\Program::count();
        $singleProgramId = $programsCount === 1 ? \App\Models\Program::first()->id : null;

        return [
            ActionGroup::make([
                Action::make('pdf')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn () => route('programs.pdf', ['program' => Session::get('hse_program_id', $singleProgramId)]))
                    ->openUrlInNewTab(),
                Action::make('excel')
                    ->label('Descargar Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn () => route('programs.excel', ['program' => Session::get('hse_program_id', $singleProgramId)]))
                    ->openUrlInNewTab(),
            ])
            ->label('Reporte del Programa')
            ->icon('heroicon-o-document-arrow-down')
            ->button()
            ->visible(fn () => Session::has('hse_program_id') || $programsCount === 1),
        ];
    }
}
