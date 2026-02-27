<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Services\Laporan\ApproveLaporanByWaliService;

class LaporanApprovalController extends Controller
{
    /**
     * Wali kelas approve laporan
     */
    public function approveByWali(
        Laporan $laporan,
        ApproveLaporanByWaliService $service
    ) {
        $this->authorize('approveByWali', $laporan);

        $service->execute($laporan, auth()->user());

        return response()->json([
            'message' => 'Laporan berhasil diterima wali kelas',
        ]);
    }
}
