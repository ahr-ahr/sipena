<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jabatan')->insert([
            [
                'id' => 1,
                'nama_jabatan' => 'Kepala Sekolah',
            ],
            [
                'id' => 2,
                'nama_jabatan' => 'IT',
            ],
            [
                'id' => 3,
                'nama_jabatan' => 'TU',
            ],
            [
                'id' => 4,
                'nama_jabatan' => 'Sarpras',
            ],
            [
                'id' => 5,
                'nama_jabatan' => 'Wali Kelas',
            ],
            [
                'id' => 6,
                'nama_jabatan' => 'Bimbingan Konseling',
            ],
            [
                'id' => 7,
                'nama_jabatan' => 'Guru',
            ],
            [
                'id' => 8,
                'nama_jabatan' => 'Kesiswaan',
            ],
        ]);
    }
}
