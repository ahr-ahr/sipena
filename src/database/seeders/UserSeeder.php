<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('users')->insert([
            [
                'id' => 1,
                'uuid' => '00000000-0000-0000-0000-000000000001',
                'email' => 'siswa@smktag.sch.id',
                'password' => Hash::make('password'),
                'tipe_user' => UserType::SISWA->value,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 2,
                'uuid' => '00000000-0000-0000-0000-000000000002',
                'email' => 'it@smktag.sch.id',
                'password' => Hash::make('password'),
                'tipe_user' => UserType::PEGAWAI->value,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 3,
                'uuid' => '00000000-0000-0000-0000-000000000003',
                'email' => 'tu@smktag.sch.id',
                'password' => Hash::make('password'),
                'tipe_user' => UserType::PEGAWAI->value,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 4,
                'uuid' => '00000000-0000-0000-0000-000000000004',
                'email' => 'sarpras@smktag.sch.id',
                'password' => Hash::make('password'),
                'tipe_user' => UserType::PEGAWAI->value,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 5,
                'uuid' => '00000000-0000-0000-0000-000000000005',
                'email' => 'wali_kelas@smktag.sch.id',
                'password' => Hash::make('password'),
                'tipe_user' => UserType::PEGAWAI->value,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 6,
                'uuid' => '00000000-0000-0000-0000-000000000006',
                'email' => 'kepala_sekolah@smktag.sch.id',
                'password' => Hash::make('password'),
                'tipe_user' => UserType::PEGAWAI->value,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 7,
                'uuid' => '00000000-0000-0000-0000-000000000007',
                'email' => 'bk@smktag.sch.id',
                'password' => Hash::make('password'),
                'tipe_user' => UserType::PEGAWAI->value,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
