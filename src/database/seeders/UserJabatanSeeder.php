<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserJabatanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_jabatan')->insert([
            [
                'user_id' => 2,
                'jabatan_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'user_id' => 3,
                'jabatan_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'user_id' => 4,
                'jabatan_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'user_id' => 5,
                'jabatan_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'user_id' => 6,
                'jabatan_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
