<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketCategory;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Hardware',
                'description' => 'Kerusakan perangkat fisik seperti PC, laptop, monitor, dll.',
            ],
            [
                'name' => 'Software',
                'description' => 'Masalah aplikasi, sistem operasi, atau instalasi software.',
            ],
            [
                'name' => 'Jaringan',
                'description' => 'Gangguan koneksi internet, LAN, WiFi, atau konfigurasi jaringan.',
            ],
            [
                'name' => 'Printer',
                'description' => 'Masalah printer seperti macet, tidak terdeteksi, atau tinta.',
            ],
            [
                'name' => 'Akun & Login',
                'description' => 'Masalah akun, password, atau akses sistem.',
            ],
            [
                'name' => 'Server',
                'description' => 'Gangguan server, hosting, atau sistem utama.',
            ],
            [
                'name' => 'Website',
                'description' => 'Masalah pada website sekolah atau aplikasi online.',
            ],
            [
                'name' => 'Lainnya',
                'description' => 'Masalah teknis lain yang tidak termasuk kategori di atas.',
            ],
        ];

        foreach ($data as $item) {
            TicketCategory::firstOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
