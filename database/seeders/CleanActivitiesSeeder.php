<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ActivityExecution;
use App\Models\Activity;
use App\Models\ProgramComponent;
use App\Models\Program;
use App\Models\Documentation;
use App\Models\OperationalControl;
use App\Models\Promotion;
use App\Models\Training;
use App\Models\Inspection;
use App\Models\Audit;
use App\Models\Drill;
use App\Models\Incident;
use App\Models\Meeting;

class CleanActivitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desactivar restricciones de clave foránea
        Schema::disableForeignKeyConstraints();

        // Truncar tablas dependientes (hijas) primero
        ActivityExecution::truncate();
        
        // Delete specific activity type tables
        Documentation::truncate();
        OperationalControl::truncate();
        Promotion::truncate();
        Training::truncate();
        Inspection::truncate();
        if (class_exists(Audit::class)) Audit::truncate();
        if (class_exists(Drill::class)) Drill::truncate();
        if (class_exists(Incident::class)) Incident::truncate();
        if (class_exists(Meeting::class)) Meeting::truncate();

        // Truncar tablas principales
        Activity::truncate();
        ProgramComponent::truncate();
        Program::truncate();

        // Reactivar restricciones de clave foránea
        Schema::enableForeignKeyConstraints();

        $this->command->info('Database cleaned successfully (Activities, Executions, Components, Programs).');
    }
}
