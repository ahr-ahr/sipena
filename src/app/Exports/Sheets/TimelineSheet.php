<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TimelineSheet implements FromCollection, WithHeadings
{
    public function __construct(private array $timeline) {}

    public function collection()
    {
        return collect($this->timeline['labels'])->map(function ($label, $i) {
            return [
                $label,
                $this->timeline['laporan'][$i] ?? 0,
                $this->timeline['ticket'][$i] ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return ['Periode', 'Laporan', 'Tiket'];
    }
}
