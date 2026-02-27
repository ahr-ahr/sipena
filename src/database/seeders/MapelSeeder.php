<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('mapel')->insert([
            [
                'kode' => 'MTK',
                'nama' => 'Matematika',
            ],
            [
                'kode' => 'BI',
                'nama' => 'Bahasa Indonesia',
            ],
            [
                'kode' => 'BING',
                'nama' => 'Bahasa Inggris',
            ],
            [
                'kode' => 'IPAS',
                'nama' => 'Ilmu Pengetahuan Alam & Sosial',
            ],
            [
                'kode' => 'PKN',
                'nama' => 'Pendidikan Kewarganegaraan',
            ],
            [
                'kode' => 'PAI',
                'nama' => 'Pendidikan Agama',
            ],
            [
                'kode' => 'INF',
                'nama' => 'Informatika',
            ],
            [
                'kode' => 'PJOK',
                'nama' => 'Pendidikan Jasmani',
            ],
            [
                'kode' => 'SBUD',
                'nama' => 'Seni Budaya',
            ],
            [
                'kode' => 'BDER',
                'nama' => 'Bahasa Daerah',
            ],
            [
                'kode' => 'UIUX',
                'nama' => 'User Interface & User Experience',
            ],
            [
                'kode' => 'TECN',
                'nama' => 'Technopreneurship',
            ],
            [
                'kode' => 'KKA',
                'nama' => 'Koding & Kecerdasan Artifisial',
            ],
            [
                'kode' => 'ALGP',
                'nama' => 'Algoritma Pemrograman',
            ],
        ]);
    }
}
