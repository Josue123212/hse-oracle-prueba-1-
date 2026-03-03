<?php

namespace App\Exports;

use App\Models\Program;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProgramExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $program;

    public function __construct(Program $program)
    {
        $this->program = $program;
    }

    public function view(): View
    {
        return view('exports.program', [
            'program' => $this->program
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set default font
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Calibri');
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(10);

                // Freeze panes (Header rows stay visible - Data starts at Row 11)
                $sheet->freezePane('A11'); 

                // Get highest row and column
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn(); // Should be AI (35)
                $fullRange = 'A1:' . $highestColumn . $highestRow;
                
                // Global Alignment
                $sheet->getStyle($fullRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($fullRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Apply borders to the table content (starting from header row 9)
                $dataRange = 'A9:' . $highestColumn . $highestRow;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Specific adjustments
                
                // Objective General (Row 7) - Left align text, wrap
                $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A7')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle('A7')->getAlignment()->setWrapText(true);
                // Ensure row height is enough for objective text
                $sheet->getRowDimension(7)->setRowHeight(60);
                
                // Column A: Objectives Specificos - Left align, Wrap
                $sheet->getStyle('A11:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A11:A' . $highestRow)->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('A')->setWidth(30);

                // Column B: Item - Center align (Default is OK)
                $sheet->getColumnDimension('B')->setWidth(8);

                // Column C: Activities - Left align, Wrap
                $sheet->getStyle('C11:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('C11:C' . $highestRow)->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('C')->setWidth(40);

                // Column D: Meta - Center
                $sheet->getColumnDimension('D')->setWidth(10);

                // Column E: Sede - Center
                $sheet->getColumnDimension('E')->setWidth(15);

                // Columns F, G, H (Responsables/Apoyo) - Center
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);

                // Column I: Frecuencia
                $sheet->getColumnDimension('I')->setWidth(12);

                // Month Columns (J to AG) - Small width
                // J, K, L, M... 
                // Loop through columns 10 to 33 (J=10, AG=33)
                for ($col = 10; $col <= 33; $col++) {
                    $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($colString)->setWidth(4);
                }

                // Metadata Header Styling (Rows 2-5, Cols AH-AI)
                // Labels (AH) - Right Align, Bold
                $sheet->getStyle('AH2:AH5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('AH2:AH5')->getFont()->setBold(true);
                
                // Values (AI) - Left Align
                $sheet->getStyle('AI2:AI5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Header styles (Row 9) - Blue background, White text is handled in Blade but we reinforce here
                $headerRange = 'A9:AI9';
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF003366');
            },
        ];
    }
}
