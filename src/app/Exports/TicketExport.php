<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithCustomStartCell,
    WithStyles
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketExport implements
    FromCollection,
    WithHeadings,
    WithCustomStartCell,
    WithStyles
{
    public function __construct(
        private Collection $tickets
    ) {}

    /**
     * DATA
     */
    public function collection()
    {
        return $this->tickets->map(function ($t) {
            return [
                $t->ticket_number,
                $t->title,
                $t->category?->name,
                strtoupper($t->priority),
                ucfirst($t->status),
                $t->created_at->format('d/m/Y'),
            ];
        });
    }

    /**
     * HEADER KOLOM
     */
    public function headings(): array
    {
        return [
            'No Tiket',
            'Judul',
            'Kategori',
            'Prioritas',
            'Status',
            'Tanggal',
        ];
    }

    /**
     * START CELL
     */
    public function startCell(): string
    {
        return 'A4';
    }

    /**
     * STYLE
     */
    public function styles(Worksheet $sheet)
    {
        // Judul
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue(
            'A1',
            'Laporan Tiket SIPENA – SMK 17 Agustus 1945 Surabaya'
        );

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
        ]);

        // Printed at
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue(
            'A2',
            'Dicetak: ' . now()->format('d-m-Y H:i')
        );

        // Separator
        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', str_repeat('-', 80));

        // Header bold
        $sheet->getStyle('A4:F4')->applyFromArray([
            'font' => ['bold' => true],
        ]);
    }
}
