<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function edit()
    {
        $settings = SystemSetting::first() ?? new SystemSetting([
            'app_name'         => config('app.name', 'Court MS'),
            'short_name'       => 'CMS',
            'contact_email'    => null,
            'contact_phone'    => null,
            'about'            => null,
            'maintenance_mode' => 0,
        ]);

        return view('admin.settings.system', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'app_name'       => ['required', 'string', 'max:255'],
            'short_name'     => ['nullable', 'string', 'max:50'],
            'about'          => ['nullable', 'string'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:50'],
            'maintenance_mode' => ['nullable', 'boolean'],
            'logo'           => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon'        => ['nullable', 'image', 'mimes:png,ico', 'max:512'],
        ]);

        $settings = SystemSetting::first() ?? new SystemSetting();

        $settings->app_name        = $data['app_name'];
        $settings->short_name      = $data['short_name'] ?? null;
        $settings->about           = $data['about'] ?? null;
        $settings->contact_email   = $data['contact_email'] ?? null;
        $settings->contact_phone   = $data['contact_phone'] ?? null;
        $settings->maintenance_mode = $request->boolean('maintenance_mode');

        // Logo upload -> logo_path
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $settings->logo_path = $path;
        }

        // Favicon upload -> favicon_path
        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('favicons', 'public');
            $settings->favicon_path = $path;
        }

        $settings->save();

        return redirect()
            ->route('settings.system.edit')
            ->with('ok', 'System settings updated.');
    }
}
