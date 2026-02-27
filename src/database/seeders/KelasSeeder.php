<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kelas')->insert([
            // KELAS X
            ['nama' => 'X-RPL-1',  'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'X-RPL-2',  'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'X-RPL-3',  'created_at' => now(), 'updated_at' => now()],

            // KELAS XI
            ['nama' => 'XI-RPL-1', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'XI-RPL-2', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'XI-RPL-3', 'created_at' => now(), 'updated_at' => now()],

            // KELAS XII
            ['nama' => 'XII-RPL-1','created_at' => now(), 'updated_at' => now()],
            ['nama' => 'XII-RPL-2','created_at' => now(), 'updated_at' => now()],
            ['nama' => 'XII-RPL-3','created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
