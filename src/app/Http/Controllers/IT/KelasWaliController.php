<?php

namespace App\Http\Controllers\IT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\User;
use App\Models\KelasWali;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Gate;

class KelasWaliController extends Controller
{
    public function __construct()
    {
        Gate::authorize('manage-users');
    }

    public function index(Request $request)
    {
        $kelas = Kelas::with(['wali.wali'])->orderBy('nama')->get();

        $waliList = User::where('tipe_user', 'pegawai')
            ->whereHas('jabatan', fn ($q) =>
                $q->whereRaw('LOWER(nama_jabatan) LIKE ?', ['%wali%'])
            )
            ->with('pegawaiProfile')
            ->orderBy('email')
            ->get();

        return view('it.kelas-wali.index', compact('kelas', 'waliList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'user_id'  => ['required', 'exists:users,id'],
        ]);

        KelasWali::updateOrCreate(
            ['kelas_id' => $data['kelas_id']],
            ['user_id'  => $data['user_id']]
        );

        AuditLogger::log(
            action: 'ASSIGN_WALI',
            targetType: 'Kelas',
            targetId: $data['kelas_id'],
            description: 'IT menugaskan wali kelas'
        );

        return response()->json([
            'message' => 'Wali kelas berhasil ditugaskan'
        ]);
    }

    public function destroy(Request $request)
{
    $data = $request->validate([
        'kelas_id' => ['required', 'exists:kelas,id'],
    ]);

    KelasWali::where('kelas_id', $data['kelas_id'])->delete();

    AuditLogger::log(
        action: 'REMOVE_WALI',
        targetType: 'Kelas',
        targetId: $data['kelas_id'],
        description: 'IT menghapus wali kelas'
    );

    return response()->json([
        'message' => 'Wali kelas berhasil dihapus'
    ]);
}
}
