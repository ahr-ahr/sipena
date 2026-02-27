<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanExport implements
    FromCollection,
    WithHeadings,
    WithCustomStartCell,
    WithStyles
{
    public function __construct(
        private Collection $laporan,
        private string $role
    ) {}

    /**
     * DATA
     */
    public function collection()
    {
        return $this->laporan->map(function ($l) {

            // SISWA → sederhana
            if ($this->role === 'siswa') {
                return [
                    $l->kode_laporan,
                    $l->judul,
                    $l->kategori?->nama,
                    $l->status->label(),
                    $l->created_at->format('d/m/Y'),
                ];
            }

            // WALI & GURU → detail
            return [
                $l->kode_laporan,
                $l->pelapor?->siswaProfile?->nama,
                $l->pelapor?->siswaProfile?->kelas?->nama,
                $l->judul,
                $l->kategori?->nama,
                $l->status->label(),
                $l->created_at->format('d/m/Y'),
            ];
        });
    }

    /**
     * HEADER KOLOM
     */
    public function headings(): array
    {
        if ($this->role === 'siswa') {
            return [
                'Kode',
                'Judul',
                'Kategori',
                'Status',
                'Tanggal',
            ];
        }

        // wali & guru
        return [
            'Kode',
            'Nama Siswa',
            'Kelas',
            'Judul',
            'Kategori',
            'Status',
            'Tanggal',
        ];
    }

    /**
     * DATA MULAI DARI BARIS KE-4
     */
    public function startCell(): string
    {
        return 'A4';
    }

    /**
     * STYLE (JUDUL + GARIS)
     */
    public function styles(Worksheet $sheet)
    {
        $colEnd = $this->role === 'siswa' ? 'E' : 'G';

        $sheet->mergeCells("A1:{$colEnd}1");
        $sheet->setCellValue(
            'A1',
            'Laporan SIPENA – SMK 17 Agustus 1945 Surabaya'
        );

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
        ]);

        $sheet->mergeCells("A2:{$colEnd}2");
        $sheet->setCellValue(
            'A2',
            'Dicetak: ' . now()->format('d-m-Y H:i')
        );

        $sheet->mergeCells("A3:{$colEnd}3");
        $sheet->setCellValue(
            'A3',
            str_repeat('-', 70)
        );

        $sheet->getStyle("A4:{$colEnd}4")->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);
    }
}
