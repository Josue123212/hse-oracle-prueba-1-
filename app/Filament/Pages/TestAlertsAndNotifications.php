<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use UnitEnum;
use BackedEnum;
use Flasher\Laravel\Facade\Flasher;

class TestAlertsAndNotifications extends Page
{
    // use SweetAlert2; // Trait removed to avoid conflicts, using manual dispatch instead

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected string $view = 'filament.pages.test-alerts-and-notifications';

    protected static ?string $navigationLabel = 'Alerta y Notificaciones Pruebas';

    protected static ?string $title = 'Alerta y Notificaciones Pruebas';

    protected static string|UnitEnum|null $navigationGroup = 'Herramientas del Sistema';

    protected static ?int $navigationSort = 99;

    public static function canAccess(): bool
    {
        return true;
    }

    // Propiedad para forzar reactividad en Livewire si es necesario
    public int $notificationCount = 0;
    public string $lastInteraction = 'Ninguna';

    // --- Métodos para ALERTAS (Modales/Interrupciones) ---

    // 1. Alerta Nativa de Filament (Modal de Confirmación)
    public function alertNative(string $type = 'warning'): void
    {
        $this->notificationCount++;
        $this->lastInteraction = "Alerta Nativa ($type) - " . now()->toTimeString();
        
        $titles = [
            'success' => 'Operación Exitosa',
            'error' => 'Error Crítico',
            'warning' => 'Advertencia de Seguridad',
            'info' => 'Información del Sistema',
        ];
        
        $bodies = [
            'success' => 'Los cambios han sido guardados correctamente en la base de datos.',
            'error' => 'No se pudo conectar con el servidor de pagos. Intente nuevamente.',
            'warning' => 'Esta acción es irreversible. ¿Desea continuar?',
            'info' => 'El sistema entrará en mantenimiento en 10 minutos.',
        ];

        // Map types for Filament
        $filamentType = match($type) {
            'error' => 'danger',
            default => $type,
        };

        Notification::make()
            ->{$filamentType}()
            ->title($titles[$type] ?? 'Alerta')
            ->body($bodies[$type] ?? '')
            ->persistent()
            ->actions([
                \Filament\Actions\Action::make('confirm')
                    ->label('Entendido')
                    ->button()
                    ->color($filamentType)
                    ->close(),
                \Filament\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->color('gray')
                    ->close(),
            ])
            ->send();
    }

    // 2. SweetAlert2 (Librería Externa)
    public function alertSweet(string $type = 'question'): void
    {
        $this->notificationCount++;
        $this->lastInteraction = "SweetAlert2 ($type) - " . now()->toTimeString();
        
        $titles = [
            'success' => '¡Buen trabajo!',
            'error' => 'Algo salió mal',
            'warning' => '¿Estás seguro?',
            'info' => 'Aviso importante',
            'question' => '¿Desea archivar este registro?'
        ];

        $texts = [
            'success' => 'Has completado la tarea exitosamente.',
            'error' => 'Hubo un error al procesar tu solicitud.',
            'warning' => 'No podrás revertir esta acción.',
            'info' => 'Tienes notificaciones pendientes de leer.',
            'question' => 'El registro pasará a estado inactivo.'
        ];
        
        $this->dispatch('swal', [
            'icon' => $type,
            'title' => $titles[$type] ?? 'Alerta',
            'text' => $texts[$type] ?? '',
            'showConfirmButton' => true,
            'showCancelButton' => $type !== 'success' && $type !== 'info', // Solo mostrar cancelar en warning/error/question
            'confirmButtonText' => 'Aceptar',
            'cancelButtonText' => 'Cancelar',
        ]);
    }

    // 3. Custom Modal (AlpineJS)
    public function alertCustom(string $type = 'info'): void
    {
        $this->notificationCount++;
        $this->lastInteraction = "Custom Modal ($type) - " . now()->toTimeString();
        
        $titles = [
            'success' => '¡Genial!',
            'error' => 'Error de Validación',
            'warning' => 'Atención',
            'info' => 'Nota Informativa',
        ];

        $bodies = [
            'success' => 'Tu perfil ha sido actualizado correctamente.',
            'error' => 'El campo "Correo" es obligatorio y debe ser válido.',
            'warning' => 'Tu sesión expirará en 5 minutos por inactividad.',
            'info' => 'La versión 2.0 ya está disponible para descarga.',
        ];

        $this->dispatch('open-custom-modal', [
            'title' => $titles[$type] ?? 'Alerta',
            'body' => $bodies[$type] ?? '',
            'type' => $type
        ]);
    }

    // 4. PHP Flasher (Librería robusta alternativa)
    public function alertFlasher(string $type = 'success'): void
    {
        $this->notificationCount++;
        $this->lastInteraction = "PHP Flasher ($type) - " . now()->toTimeString();
        
        $msg = match($type) {
            'success' => 'Operación completada con éxito via Flasher.',
            'error' => 'Ocurrió un error crítico en el sistema.',
            'warning' => 'Por favor revise los datos ingresados.',
            'info' => 'Hay nuevas actualizaciones disponibles.',
            default => 'Mensaje por defecto.'
        };

        // Enviar evento a Livewire para renderizar manualmente
        // Esto simula lo que hace el middleware pero sin romper Filament
        $envelope = [
            'type' => $type,
            'message' => $msg,
            'title' => ucfirst($type),
        ];
        
        // Estructura compatible con Flasher JS
        $flasherData = [
            'envelopes' => [
                [
                    'notification' => [
                        'type' => $type,
                        'message' => $msg,
                        'title' => ucfirst($type),
                    ],
                    'handler' => 'toastr', // Especificar el handler explícitamente
                ]
            ]
        ];

        $this->dispatch('flasher:render', $flasherData);
    }


    // --- Métodos para NOTIFICACIONES (Toasts) ---

    // 1. Notificación Nativa Filament (Toast)
    public function notificationNative(string $type = 'success'): void
    {
        $this->notificationCount++;
        $this->lastInteraction = "Notificación Nativa ($type) - " . now()->toTimeString();
        
        // Map types for Filament
        $filamentType = match($type) {
            'error' => 'danger',
            default => $type,
        };

        Notification::make()
            ->{$filamentType}()
            ->title(ucfirst($type))
            ->body("Esta es una notificación de tipo $type.")
            ->seconds(3)
            ->send();
    }

    // 2. SweetAlert Toast
    public function notificationSweet(string $type = 'success'): void
    {
        $this->notificationCount++;
        $this->lastInteraction = "SweetAlert Toast ($type) - " . now()->toTimeString();
        
        $this->dispatch('swal', [
            'icon' => $type,
            'title' => ucfirst($type),
            'position' => 'top-end',
            'timer' => 3000,
            'toast' => true,
            'showConfirmButton' => false,
            'text' => "Notificación toast de tipo $type",
        ]);
    }

    // 3. Custom Toast (AlpineJS)
    public function notificationCustom(string $type = 'success'): void
    {
        \Illuminate\Support\Facades\Log::info("Método notificationCustom llamado con tipo: $type");
        
        $this->notificationCount++;
        $this->lastInteraction = "Custom Toast ($type) - " . now()->toTimeString();
        
        $messages = [
            'success' => 'Guardado correctamente',
            'error' => 'Error de conexión',
            'warning' => 'Revisar formulario',
            'info' => 'Actualizando datos...',
        ];

        $payload = [
            'message' => $messages[$type] ?? 'Notificación',
            'type' => $type
        ];

        \Illuminate\Support\Facades\Log::info("Despachando evento 'show-custom-toast' con payload:", $payload);

        $this->dispatch('show-custom-toast', $payload);
    }

    // 4. PHP Flasher Toast (Mismo driver pero configuración diferente si es posible, o simplemente otra llamada)
    public function notificationFlasher(string $type = 'success'): void
    {
        $this->notificationCount++;
        $this->lastInteraction = "PHP Flasher Toast ($type) - " . now()->toTimeString();
        
        $msg = match($type) {
            'success' => 'Acción realizada (Toast)',
            'error' => 'Operación fallida (Toast)',
            'warning' => 'Cuidado (Toast)',
            'info' => 'Info (Toast)',
            default => 'Toast'
        };

        $flasherData = [
            'envelopes' => [
                [
                    'notification' => [
                        'type' => $type,
                        'message' => $msg,
                        'options' => [
                            'position' => 'top-right',
                        ]
                    ],
                    'handler' => 'toastr', // Especificar el handler explícitamente
                ]
            ]
        ];

        $this->dispatch('flasher:render', $flasherData);
    }

    // Mantener métodos antiguos para compatibilidad si es necesario, o redirigirlos
    public function notifySuccess(): void { $this->notificationNative(); }
    public function notifyError(): void { $this->alertCustom(); }
    public function notifyWarning(): void { $this->alertNative(); }
    public function notifyInfo(): void { $this->notificationSweet(); }

    /**
     * Función reutilizable para gestionar el estado de las notificaciones.
     */
    private function sendNotification(string $type, string $title, string $body, bool $persistent = false): void
    {
        // Incrementamos contador para asegurar que Livewire detecte cambio de estado y procese la respuesta
        $this->notificationCount++;
        // \Illuminate\Support\Facades\Log::info("Enviando notificación: $type");

        $notification = Notification::make()
            ->title($title)
            ->body($body)
            ->{$type}(); // success(), danger(), warning(), info()

        if ($persistent) {
            $notification->persistent();
        } else {
            $notification->seconds(4); // 3-5 segundos
        }

        // Intentamos enviar notificación estándar de Filament
        // try {
        //     $notification->send();
        // } catch (\Exception $e) {
        //     \Illuminate\Support\Facades\Log::error('Error enviando notificación Filament: ' . $e->getMessage());
        // }

        // También despachamos evento manual para capturar en frontend si el interceptor falla
        $this->dispatch('manual-notification', [
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'persistent' => $persistent
        ]);
    }
}
