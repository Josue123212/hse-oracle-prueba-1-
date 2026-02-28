<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Activity;
use App\Enums\ActivityState;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\Program;

class ActivityStateLogicTest extends TestCase
{
    use DatabaseTransactions;

    protected $program;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a dummy program for FK
        $this->program = Program::create([
            'nombre' => 'Test Program',
        ]);
    }

    public function test_eventual_activity_is_always_executed()
    {
        $activity = new Activity();
        $activity->program_id = $this->program->id;
        $activity->nombre = 'Test Activity';
        $activity->frecuencia = 'eventual';
        $activity->veces_al_anio = 1;
        $activity->ejecuciones_realizadas = 0;
        $activity->fecha_inicio = Carbon::now()->addDays(5);
        $activity->estado = ActivityState::PROGRAMADO;
        
        // Simulate saving
        $activity->save();
        
        $this->assertEquals(ActivityState::EJECUTADO, $activity->estado, 'Eventual activity should be forced to EJECUTADO');
    }

    public function test_past_activity_is_no_cumplio()
    {
        $activity = new Activity();
        $activity->program_id = $this->program->id;
        $activity->nombre = 'Test Activity';
        $activity->frecuencia = 'mensual';
        $activity->veces_al_anio = 12;
        $activity->ejecuciones_realizadas = 0;
        $activity->fecha_inicio = Carbon::now()->subMonth(); // Past
        $activity->estado = ActivityState::PROGRAMADO;
        
        $activity->save();
        
        $this->assertEquals(ActivityState::NO_CUMPLIO, $activity->estado, 'Past activity should be NO_CUMPLIO');
    }

    public function test_future_activity_is_programado()
    {
        $activity = new Activity();
        $activity->program_id = $this->program->id;
        $activity->nombre = 'Test Activity';
        $activity->frecuencia = 'mensual';
        $activity->veces_al_anio = 12;
        $activity->ejecuciones_realizadas = 0;
        $activity->fecha_inicio = Carbon::now()->addMonth(); // Future
        $activity->estado = ActivityState::PROGRAMADO;
        
        $activity->save();
        
        $this->assertEquals(ActivityState::PROGRAMADO, $activity->estado, 'Future activity should be PROGRAMADO');
    }

    public function test_started_activity_is_en_proceso()
    {
        $activity = new Activity();
        $activity->program_id = $this->program->id;
        $activity->nombre = 'Test Activity';
        $activity->frecuencia = 'mensual';
        $activity->veces_al_anio = 12;
        $activity->ejecuciones_realizadas = 1; // Started
        $activity->fecha_inicio = Carbon::now()->addMonth(); // Future
        $activity->estado = ActivityState::PROGRAMADO; // Should change
        
        $activity->save();
        
        $this->assertEquals(ActivityState::EN_PROCESO, $activity->estado, 'Started multi-rep activity should be EN_PROCESO');
    }

    public function test_completed_activity_is_ejecutado()
    {
        $activity = new Activity();
        $activity->program_id = $this->program->id;
        $activity->nombre = 'Test Activity';
        $activity->frecuencia = 'mensual';
        $activity->veces_al_anio = 12;
        $activity->ejecuciones_realizadas = 12; // Completed
        $activity->fecha_inicio = Carbon::now()->addMonth();
        $activity->estado = ActivityState::PROGRAMADO;
        
        $activity->save();
        
        $this->assertEquals(ActivityState::EJECUTADO, $activity->estado, 'Completed activity should be EJECUTADO');
    }
}
