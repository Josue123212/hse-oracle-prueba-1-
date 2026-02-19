<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\FileStorageService;

class InspectionPdfController extends Controller
{
    public function download(Inspection $inspection, FileStorageService $files)
    {
        // Load relationships needed for the PDF
        $inspection->load([
            'inspectionType', 
            'activity', 
            'location', 
            'user'
        ]);

        $pdf = Pdf::loadView('pdf.inspection', compact('inspection'));
        $bytes = $pdf->output();
        $year = (string) $inspection->anio;
        $month = str_pad((string) $inspection->mes, 2, '0', STR_PAD_LEFT);
        $filename = "inspeccion-{$inspection->id}.pdf";
        $path = "inspecciones/{$year}/{$month}/{$filename}";
        $files->storePublic($path, $bytes);
        $inspection->archivo_detectado = true;
        $inspection->save();
        
        return $pdf->download("inspeccion-{$inspection->id}.pdf");
    }
}
