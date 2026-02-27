<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SummarySheet implements FromCollection, WithHeadings
{
    public function __construct(private array $stats) {}

    public function collection()
    {
        return collect([
            ['Laporan', 'Selesai', $this->stats['laporan']['selesai']],
            ['Laporan', 'Ditolak', $this->stats['laporan']['ditolak']],
            ['Tiket', 'Closed', $this->stats['ticket']['closed']],
            ['Tiket', 'Resolved', $this->stats['ticket']['resolved']],
        ]);
    }

    public function headings(): array
    {
        return ['Jenis', 'Status', 'Total'];
    }
}
