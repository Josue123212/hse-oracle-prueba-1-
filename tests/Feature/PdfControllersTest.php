<?php

use App\Models\Program;
use App\Models\Inspection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('guarda y expone PDF de inspección al descargar', function () {
    Storage::fake('public');

    $inspection = Inspection::create([
        'mes' => 2,
        'anio' => 2026,
        'fecha_programada' => Carbon::parse('2026-02-10')->toDateString(),
        'estado' => 'pendiente',
        'observaciones' => null,
        'archivo_detectado' => false,
    ]);

    $response = $this->get(route('inspections.pdf', $inspection));
    $response->assertStatus(200);

    $expectedPath = "inspecciones/{$inspection->anio}/" . str_pad((string) $inspection->mes, 2, '0', STR_PAD_LEFT) . "/inspeccion-{$inspection->id}.pdf";
    Storage::disk('public')->assertExists($expectedPath);

    $inspection->refresh();
    expect($inspection->archivo_detectado)->toBeTrue();
});

it('guarda PDF de programa al descargar', function () {
    Storage::fake('public');

    $program = Program::create([
        'codigo' => 'PRG-001',
        'version' => '1.0',
        'obj_general' => 'Objetivo de prueba',
        'autor' => 'Tester',
        'fecha_emision' => Carbon::parse('2026-02-01')->toDateString(),
    ]);

    $response = $this->get(route('programs.pdf', $program));
    $response->assertStatus(200);

    $year = 2026;
    $month = '02';
    $expectedPath = "programas/{$year}/{$month}/programa-{$program->codigo}.pdf";
    Storage::disk('public')->assertExists($expectedPath);
});

