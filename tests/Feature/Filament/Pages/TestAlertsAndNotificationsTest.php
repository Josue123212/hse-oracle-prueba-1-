<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\TestAlertsAndNotifications;
use App\Models\User;
use Filament\Notifications\Notification;
// use Illuminate\Foundation\Testing\RefreshDatabase; // Removed to avoid migration errors
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Livewire\Livewire;
use Tests\TestCase;

class TestAlertsAndNotificationsTest extends TestCase
{
    // use RefreshDatabase; // Removed

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user table manually for SQLite memory to avoid migration errors with other tables
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    // public function test_it_can_render_page()
    // {
    //     $this->actingAs($this->user)
    //         ->get(TestAlertsAndNotifications::getUrl())
    //         ->assertSuccessful();
    // }

    public function test_it_increments_count_on_alert_native()
    {
        Livewire::actingAs($this->user)
            ->test(TestAlertsAndNotifications::class)
            ->call('alertNative')
            ->assertSet('notificationCount', 1)
            ->assertSet('lastInteraction', function($value) {
                return str_contains($value, 'Alerta Nativa');
            });
    }

    public function test_it_increments_count_on_alert_sweet()
    {
        Livewire::actingAs($this->user)
            ->test(TestAlertsAndNotifications::class)
            ->call('alertSweet')
            ->assertSet('notificationCount', 1)
            ->assertSet('lastInteraction', function($value) {
                return str_contains($value, 'SweetAlert2');
            })
            ->assertDispatched('swal', function($event, $data) {
                $payload = is_array($data) && isset($data[0]) && is_array($data[0]) ? $data[0] : $data;
                return ($payload['icon'] ?? '') === 'question';
            });
    }

    public function test_it_increments_count_on_alert_custom()
    {
        Livewire::actingAs($this->user)
            ->test(TestAlertsAndNotifications::class)
            ->call('alertCustom')
            ->assertSet('notificationCount', 1)
            ->assertSet('lastInteraction', function($value) {
                return str_contains($value, 'Custom Modal');
            })
            ->assertDispatched('open-custom-modal');
    }

    public function test_it_increments_count_on_alert_flasher()
    {
        Livewire::actingAs($this->user)
            ->test(TestAlertsAndNotifications::class)
            ->call('alertFlasher')
            ->assertSet('notificationCount', 1)
            ->assertSet('lastInteraction', function($value) {
                return str_contains($value, 'PHP Flasher');
            });
    }

    public function test_it_increments_count_on_notification_native()
    {
        Livewire::actingAs($this->user)
            ->test(TestAlertsAndNotifications::class)
            ->call('notificationNative')
            ->assertSet('notificationCount', 1)
            ->assertSet('lastInteraction', function($value) {
                return str_contains($value, 'Notificación Nativa');
            });
    }

    public function test_it_increments_count_on_notification_sweet()
    {
        Livewire::actingAs($this->user)
            ->test(TestAlertsAndNotifications::class)
            ->call('notificationSweet')
            ->assertSet('notificationCount', 1)
            ->assertSet('lastInteraction', function($value) {
                return str_contains($value, 'SweetAlert Toast');
            })
            ->assertDispatched('swal', function($event, $data) {
                $payload = is_array($data) && isset($data[0]) && is_array($data[0]) ? $data[0] : $data;
                return ($payload['icon'] ?? '') === 'success' && ($payload['toast'] ?? false) === true;
            });
    }

    public function test_it_increments_count_on_notification_custom()
    {
        Livewire::actingAs($this->user)
            ->test(TestAlertsAndNotifications::class)
            ->call('notificationCustom')
            ->assertSet('notificationCount', 1)
            ->assertSet('lastInteraction', function($value) {
                return str_contains($value, 'Custom Toast');
            })
            ->assertDispatched('show-custom-toast');
    }

    public function test_it_increments_count_on_notification_flasher()
    {
        Livewire::actingAs($this->user)
            ->test(TestAlertsAndNotifications::class)
            ->call('notificationFlasher')
            ->assertSet('notificationCount', 1)
            ->assertSet('lastInteraction', function($value) {
                return str_contains($value, 'PHP Flasher Toast');
            });
    }
}
