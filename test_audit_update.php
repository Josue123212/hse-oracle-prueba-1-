<?php

use App\Models\Audit;
use App\Models\Activity;
use App\Models\Location;
use App\Services\ActivityService;
use App\Models\ProgramComponent;
use App\Models\Supervisor;
use App\Models\Position;

// Setup
$location1 = Location::first();
$location2 = Location::create(['nombre' => 'Update Location', 'direccion' => 'Address', 'activo' => true]);
$component = ProgramComponent::first();
$supervisor = Supervisor::first();
$position = Position::first();

// Create
$data = [
    'nombre' => 'Test Audit Update',
    'program_component_id' => $component->id,
    'location_id' => $location1->id,
    'responsable_delegado_id' => $position->id,
    'apoyo' => 'Initial Support',
    'auditor_id' => $supervisor->id,
    'tipo' => 'auditoria',
    'frecuencia' => 'mensual',
    'veces_al_anio' => 12,
    'meta' => 100,
    'unidad_medida' => 'Porcentaje',
    'es_obligatoria' => true,
    'ejecuciones_realizadas' => 0,
];

$service = new ActivityService();
$audit = $service->createWithType($data, 'auditoria');
$activity = $audit->activity;

echo "Initial Location: " . $activity->location_id . "\n";

// Update via Audit model (simulating Filament)
// Filament does: $audit->fill($data)->save();
$audit->fill([
    'location_id' => $location2->id,
    'apoyo' => 'Updated Support'
]);
$audit->save();

// Verify
$activity->refresh();
echo "Updated Location: " . $activity->location_id . " (Expected: " . $location2->id . ")\n";
echo "Updated Apoyo: " . $activity->apoyo . " (Expected: Updated Support)\n";

if ($activity->location_id == $location2->id && $activity->apoyo == 'Updated Support') {
    echo "SUCCESS\n";
} else {
    echo "FAILURE\n";
}
