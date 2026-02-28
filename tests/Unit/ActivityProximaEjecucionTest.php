<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Activity;
use App\Models\Inspection;
use App\Models\Program;
use App\Models\User;
use App\Enums\ActivityState;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ActivityProximaEjecucionTest extends TestCase
{
    use DatabaseTransactions;

    protected $program;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create required dependencies
        $this->program = Program::create([
            'nombre' => 'Test Program',
            'fecha_inicio' => Carbon::now(),
            'fecha_fin' => Carbon::now()->addYear(),
        ]);

        $this->user = User::first() ?? User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_it_calculates_proxima_ejecucion_on_save()
    {
        $startDate = Carbon::today()->addDays(5);
        
        $activity = Activity::create([
            'program_id' => $this->program->id,
            'responsable_id' => $this->user->id,
            'nombre' => 'Test Activity',
            'tipo' => 'inspeccion',
            'frecuencia' => 'mensual',
            'veces_al_anio' => 12,
            'ejecuciones_realizadas' => 0,
            'fecha_inicio' => $startDate,
            'fecha_fin' => $startDate->copy()->addYear(),
            'estado' => ActivityState::PROGRAMADO,
        ]);

        // Accessor logic: next execution date is start date if 0 executions
        $this->assertEquals($startDate->format('Y-m-d'), $activity->proxima_ejecucion->format('Y-m-d'));
    }

    public function test_it_sets_proxima_ejecucion_null_for_eventual_activities()
    {
        $startDate = Carbon::today()->addDays(5);
        
        $activity = Activity::create([
            'program_id' => $this->program->id,
            'responsable_id' => $this->user->id,
            'nombre' => 'Eventual Activity',
            'tipo' => 'inspeccion',
            'frecuencia' => 'eventual',
            'fecha_inicio' => $startDate,
            'fecha_fin' => $startDate->copy()->addYear(),
            'estado' => ActivityState::PROGRAMADO, // Observer should change this to EJECUTADO
        ]);

        $this->assertNull($activity->proxima_ejecucion);
        $this->assertEquals(ActivityState::EJECUTADO, $activity->estado);
    }

    public function test_it_syncs_proxima_ejecucion_to_child_model()
    {
        $startDate = Carbon::today()->addDays(10);
        
        // Create Activity which triggers Inspection creation (if observer logic for creation exists)
        // However, ActivityObserver 'created' method usually doesn't create the child.
        // We might need to manually create the child and link it, or rely on the app's logic.
        // Assuming the app creates the child separately or the test needs to do it.
        
        $activity = Activity::create([
            'program_id' => $this->program->id,
            'responsable_id' => $this->user->id,
            'nombre' => 'Parent Activity',
            'tipo' => 'inspeccion',
            'frecuencia' => 'mensual',
            'veces_al_anio' => 12,
            'ejecuciones_realizadas' => 0,
            'fecha_inicio' => $startDate,
            'fecha_fin' => $startDate->copy()->addYear(),
            'estado' => ActivityState::PROGRAMADO,
        ]);

        // Manually create the child inspection linked to the activity
        $inspection = Inspection::create([
            'program_id' => $this->program->id,
            'activity_id' => $activity->id,
            'nombre' => 'Child Inspection',
            'fecha_programada' => $startDate,
            'estado' => ActivityState::PROGRAMADO,
            'responsable_id' => $this->user->id,
        ]);
        
        // Reload activity to get the relationship
        $activity = $activity->fresh();
        
        // Update activity to trigger 'updated' observer
        $newStartDate = Carbon::today()->addDays(20);
        $activity->update([
            'fecha_inicio' => $newStartDate,
        ]);

        // Refresh inspection to check changes
        $inspection->refresh();

        // Check if inspection's proxima_ejecucion was updated
        // Note: activity's proxima_ejecucion should be $newStartDate
        $this->assertEquals($newStartDate->format('Y-m-d'), $activity->proxima_ejecucion->format('Y-m-d'));
        $this->assertEquals($newStartDate->format('Y-m-d'), $inspection->proxima_ejecucion->format('Y-m-d'));
    }
}
