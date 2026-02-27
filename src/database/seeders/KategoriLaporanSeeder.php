<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriLaporan;

class KategoriLaporanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Pengaduan Fasilitas',
            'Pengaduan Akademik',
            'Pengaduan Disiplin',
            'Aspirasi Siswa',
            'Laporan Keamanan',
            'Lainnya',
        ];

        foreach ($data as $nama) {
            KategoriLaporan::firstOrCreate([
                'nama' => $nama,
            ]);
        }
    }
}
