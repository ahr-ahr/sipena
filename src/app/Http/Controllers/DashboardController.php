<?php

namespace App\Http\Controllers;

use App\Models\{
    Ticket,
    Laporan,
    KategoriLaporan,
    NotificationRecipient,
    Mapel
};
use App\Enums\{
    UserType,
    LaporanStatus
};

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        /* ================= PROFILE PROGRESS ================= */

        $progress = 0;
        $checks = [];

        if ($user->hasVerifiedEmail()) {
            $progress += 30;
            $checks['email'] = true;
        } else {
            $checks['email'] = false;
        }

        if ($user->tipe_user === UserType::SISWA) {
            $profile = $user->siswaProfile;

            if ($profile?->nama) {
                $progress += 40;
                $checks['nama'] = true;
            } else $checks['nama'] = false;

            if ($profile?->nis) {
                $progress += 30;
                $checks['identitas'] = true;
            } else $checks['identitas'] = false;

        } else {
            $profile = $user->pegawaiProfile;

            if ($profile?->nama) {
                $progress += 40;
                $checks['nama'] = true;
            } else $checks['nama'] = false;

            if ($profile?->nip) {
                $progress += 30;
                $checks['identitas'] = true;
            } else $checks['identitas'] = false;
        }

        /* ================= STATISTIK ================= */

        if ($user->tipe_user === UserType::SISWA) {

            // Statistik milik siswa
            $laporanTotal = Laporan::where('pelapor_id', $user->id)->count();

            $ticketTotal = Ticket::where('user_id', $user->id)->count();

            $onProgress = Laporan::where('pelapor_id', $user->id)
                ->whereNotIn('status', [
                    LaporanStatus::SELESAI,
                    LaporanStatus::DITOLAK
                ])->count()
                +
                Ticket::where('user_id', $user->id)
                    ->whereIn('status', ['open', 'progress'])
                    ->count();

            $completed = Laporan::where('pelapor_id', $user->id)
                ->where('status', LaporanStatus::SELESAI)
                ->count()
                +
                Ticket::where('user_id', $user->id)
                    ->whereIn('status', ['closed', 'resolved'])
                    ->count();

        } else {

    // tentukan role pegawai
    $jabatanModel = $user->jabatan->first();

    $jabatan = strtolower(
        $jabatanModel?->nama_jabatan ?? ''
    );

    // mapping sederhana
    $roleMap = [
        'kesiswaan' => 'kesiswaan',
        'sarpras' => 'sarpras',
        'bimbingan konseling' => 'bk',
        'tu' => 'tu',
        'guru' => 'guru',
        'wali kelas' => 'wali',
    ];

    $currentRole = $roleMap[$jabatan] ?? null;

    if ($currentRole) {
        $laporanQuery = Laporan::where('current_role', $currentRole);
    } else {
        // fallback kalau kepala sekolah atau IT
        $laporanQuery = Laporan::query();
    }

    $laporanTotal = $laporanQuery->count();

    $ticketTotal  = Ticket::count();

    $onProgress = (clone $laporanQuery)
        ->whereNotIn('status', [
            LaporanStatus::SELESAI,
            LaporanStatus::DITOLAK
        ])->count();

    $completed = (clone $laporanQuery)
        ->where('status', LaporanStatus::SELESAI)
        ->count();
}


        /* ================= MINI CHART ================= */

        $chart = [
            'laporan' => $laporanTotal,
            'tiket'   => $ticketTotal,
        ];

        /* ================= NOTIFIKASI ================= */

        $notifications = NotificationRecipient::with('notification')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        /* ================= MAPEL SISWA ================= */

        $mapelList = collect();

        if ($user->tipe_user === UserType::SISWA) {
            $kelas = $user->siswaProfile?->kelas;

            if ($kelas) {
                $mapelList = $kelas->mapel()
                    ->orderBy('nama')
                    ->get();
            }
        }

        return view('dashboard', [
            'laporanCategories' => KategoriLaporan::orderBy('nama')->get(),

            'profileProgress' => $progress,
            'profileChecks'   => $checks,

            'laporanTotal' => $laporanTotal,
            'ticketTotal'  => $ticketTotal,
            'onProgress'   => $onProgress,
            'completed'    => $completed,

            'chartData' => $chart,
            'notifications' => $notifications,
            'mapelList' => $mapelList,
        ]);
    }
}
