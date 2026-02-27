<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::firstOrCreate(
            ['id' => 1],
            [
                'app_name' => 'SIPENA',
                'app_short_name' => 'SIPENA',
                'school_name' => 'SMK 17 Agustus 1945',
                'app_version' => '1.0.0',
                'maintenance_mode' => false,
            ]
        );
    }
}
