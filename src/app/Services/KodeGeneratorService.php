<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KodeGeneratorService
{
    public function generateLaporan(): string
    {
        return DB::transaction(function () {
            $year = now()->year;

            $count = DB::table('laporan')->whereYear('created_at', $year)->lockForUpdate()->count();

            $next = $count + 1;

            return sprintf('LPR-%d-%06d', $year, $next);
        });
    }

    public function generateAnggaran(): string
    {
        return DB::transaction(function () {
            $year = now()->year;

            $count = DB::table('anggaran')->whereYear('created_at', $year)->lockForUpdate()->count();

            $next = $count + 1;

            return sprintf('AGR-%d-%06d', $year, $next);
        });
    }
}

?>