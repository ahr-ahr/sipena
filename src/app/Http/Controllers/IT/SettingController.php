<?php

namespace App\Http\Controllers\IT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    public function edit()
    {
        Gate::authorize('manage-settings');
        $app = AppSetting::first();

        return view('it.settings.index', [
            'app' => $app,
        ]);
    }

    public function update(Request $request)
    {
        Gate::authorize('manage-settings');
        $request->validate([
            'app_name'        => 'required|string|max:100',
            'app_short_name'  => 'nullable|string|max:50',
            'school_logo'     => 'nullable|image|max:2048',
            'seo_description' => 'nullable|string|max:255',
            'seo_keywords'    => 'nullable|string|max:255',
        ]);

        // ===== APP SETTINGS =====
        $app = AppSetting::first();

        $app->update([
            'app_name'       => $request->app_name,
            'app_short_name' => $request->app_short_name,
        ]);

        if ($request->hasFile('school_logo')) {

            $path = $request->file('school_logo')
                ->store('public/logos', 'minio');

            $app->update([
                'school_logo' => $path,
            ]);
        }

        // ===== SYSTEM SETTINGS (SEO) =====
        SystemSetting::set('seo_description', $request->seo_description, 'seo');
        SystemSetting::set('seo_keywords', $request->seo_keywords, 'seo');

        return back()->with('success', 'Pengaturan berhasil disimpan');
    }
}
