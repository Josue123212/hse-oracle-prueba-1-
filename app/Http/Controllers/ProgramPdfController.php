<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\FileStorageService;
use Illuminate\Support\Carbon;
use App\Exports\ProgramExport;
use Maatwebsite\Excel\Facades\Excel;

class ProgramPdfController extends Controller
{
    public function downloadExcel(Program $program)
    {
        $program->load([
            'components.activities.location',
            'components.activities.responsable',
            'components.activities.responsableDelegado',
            'components.activities.executions',
            'supervisor'
        ]);

        return Excel::download(new ProgramExport($program), "programa-{$program->codigo}.xlsx");
    }

    public function download(Program $program, FileStorageService $files)
    {
        // Load relationships needed for the PDF
        $program->load([
            'components.activities.location',
            'components.activities.responsable',
            'components.activities.responsableDelegado',
            'components.activities.executions',
            'supervisor'
        ]);

        $pdf = Pdf::loadView('pdf.program', compact('program'));
        $pdf->setPaper('a4', 'landscape'); // Mejor landscape para tablas anchas
        $bytes = $pdf->output();
        $date = $program->fecha_emision ? Carbon::parse($program->fecha_emision) : Carbon::now();
        $year = $date->year;
        $month = str_pad((string) $date->month, 2, '0', STR_PAD_LEFT);
        $filename = "programa-{$program->codigo}.pdf";
        $path = "programas/{$year}/{$month}/{$filename}";
        $files->storePublic($path, $bytes);
        
        return $pdf->download("programa-{$program->codigo}.pdf");
    }
}
