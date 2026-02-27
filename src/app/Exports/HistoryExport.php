<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HistoryExport implements WithMultipleSheets
{
    public function __construct(
        private Collection $rows,
        private array $stats,
        private array $timeline
    ) {}

    public function sheets(): array
    {
        return [
            new Sheets\HistorySheet($this->rows),
            new Sheets\SummarySheet($this->stats),
            new Sheets\TimelineSheet($this->timeline),
        ];
    }
}
