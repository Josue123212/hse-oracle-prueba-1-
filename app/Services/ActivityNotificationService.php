<?php

namespace App\Services;

use App\Models\ActivityExecution;
use App\Enums\ActivityState;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class ActivityNotificationService
{
    /**
     * Comprueba y envía notificaciones sobre el estado de las actividades.
     * Sigue una jerarquía estricta: Vencidas > Hoy > Próximas > Eventuales.
     * 
     * @param string|null $activityType Filtrar por tipo de actividad (ej. 'inspeccion', 'auditoria')
     */
    public function checkAndNotify(?string $activityType = null): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        // Base Query Builder
        $queryBase = ActivityExecution::query()
            ->when($activityType, function ($q) use ($activityType) {
                $q->whereHas('activity', fn ($sq) => $sq->where('tipo', $activityType));
            });

        // Dynamic Labels based on type
        $label = $activityType ? $this->getActivityLabel($activityType) : 'actividades';
        $Label = ucfirst($label);

        // 1. Critical: Overdue Activities
        $overdueCount = (clone $queryBase)
            ->whereDate('fecha_programada', '<', now()->startOfDay())
            ->whereIn('estado', [ActivityState::PROGRAMADO, ActivityState::EN_PROCESO])
            ->count();

        if ($overdueCount > 0) {
            $this->sendNotification(
                title: "Atención: {$Label} Vencidas",
                body: "Tienes {$overdueCount} {$label} vencidas.",
                type: 'danger',
                persistent: true,
                user: $user,
                // Siempre llevamos al Dashboard con scroll al widget, sin abrir modal.
                scrollTo: 'overdue-activities-widget'
            );
            // Ya no retornamos aquí, permitimos que continúe a "Hoy"
        }

        // 2. Warning: Today's Activities
        $todayCount = (clone $queryBase)
            ->whereDate('fecha_programada', now())
            ->whereIn('estado', [ActivityState::PROGRAMADO, ActivityState::EN_PROCESO])
            ->count();

        if ($todayCount > 0) {
            $this->sendNotification(
                title: "{$Label} para Hoy",
                body: "Tienes {$todayCount} {$label} programadas para hoy.",
                type: 'warning',
                persistent: false,
                user: $user
            );
            // Ya no retornamos aquí, permitimos evaluar si mostramos las demás
        }

        // Si hay ALGUNA urgencia (Vencidas O Hoy), NO mostramos Próximas ni Eventuales
        if ($overdueCount > 0 || $todayCount > 0) {
            return;
        }

        // 3. Info: Upcoming Activities (Next 3 days)
        $upcomingCount = (clone $queryBase)
            ->whereDate('fecha_programada', '>', now())
            ->whereDate('fecha_programada', '<=', now()->addDays(3))
            ->whereIn('estado', [ActivityState::PROGRAMADO])
            ->count();

        if ($upcomingCount > 0) {
            $this->sendNotification(
                title: "Próximas {$Label}",
                body: "Tienes {$upcomingCount} {$label} programadas para los próximos 3 días.",
                type: 'info',
                persistent: false,
                user: $user
            );
            // Si hay próximas, no mostramos eventuales
            return;
        }

        // 4. Info: Eventual Activities (Lowest Priority)
        $eventualCount = (clone $queryBase)
            ->whereHas('activity', fn ($q) => $q->where('frecuencia', 'eventual'))
            ->whereIn('estado', [ActivityState::PROGRAMADO, ActivityState::EN_PROCESO])
            ->count();

        if ($eventualCount > 0) {
            $this->sendNotification(
                title: "{$Label} Eventuales",
                body: "Tienes {$eventualCount} {$label} eventuales disponibles.",
                type: 'info',
                persistent: false,
                user: $user
            );
        }
    }

    protected function getActivityLabel(string $type): string
    {
        return match ($type) {
            'inspeccion' => 'inspecciones',
            'auditoria' => 'auditorías',
            'simulacro' => 'simulacros',
            'comite' => 'comités',
            'capacitacion' => 'capacitaciones',
            'documentacion' => 'documentaciones',
            'incidente' => 'incidentes',
            default => 'actividades',
        };
    }

    protected function sendNotification(string $title, string $body, string $type, bool $persistent, $user, ?string $scrollTo = null): void
    {
        // Define actions logic
        $actions = [];
        if ($persistent) {
            if ($scrollTo) {
                // Case 1: Dashboard notification -> Scroll to widget
                $actions[] = Action::make('view')
                    ->label('Revisar')
                    ->button()
                    ->url(route('filament.admin.pages.dashboard') . '#' . $scrollTo)
                    ->close();
            } else {
                // Case 2: Specific resource notification -> Just close (already on page)
                $actions[] = Action::make('view')
                    ->label('Revisar')
                    ->button()
                    ->close();
            }
        }

        // 1. Visual Notification (Toast)
        $notification = Notification::make()
            ->title($title)
            ->body($body)
            ->$type();

        if ($persistent) {
            $notification->persistent()->actions($actions);
        }

        $notification->send();

        // 2. Database Notification (Bell) - Only if not already unread
        $hasNotification = $user->unreadNotifications
            ->contains(fn ($n) => ($n->data['title'] ?? '') === $title);

        if (!$hasNotification) {
            $dbNotification = Notification::make()
                ->title($title)
                ->body($body)
                ->$type();

            if ($persistent) {
                $dbNotification->persistent()->actions($actions);
            }
            
            $dbNotification->sendToDatabase($user);
        }
    }
}
