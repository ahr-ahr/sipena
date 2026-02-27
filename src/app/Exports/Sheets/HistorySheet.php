<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithCustomStartCell,
    WithStyles
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HistorySheet implements FromCollection, WithHeadings, WithCustomStartCell, WithStyles
{
    public function __construct(private Collection $rows) {}

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Jenis', 'Kode', 'Judul', 'Status', 'Tanggal'];
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue(
            'A1',
            'History SIPENA – SMK 17 Agustus 1945 Surabaya'
        );

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue(
            'A2',
            'Dicetak: '.now()->format('d-m-Y H:i')
        );

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A4:E4')->getFont()->setBold(true);
    }
}
