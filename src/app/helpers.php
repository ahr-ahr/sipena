<?php

use App\Models\AppSetting;
use App\Models\SystemSetting;

if (! function_exists('app_setting')) {
    function app_setting(): ?AppSetting
    {
        return AppSetting::first();
    }
}

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return SystemSetting::get($key, $default);
    }
}
