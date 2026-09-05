<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $hero = SiteSetting::get('homepage.hero', [
            'title' => 'The marketplace for high-quality code',
            'subtitle' => 'Scripts, plugins, themes, and digital assets.',
        ]);
        $site = SiteSetting::get('site', [
            'name' => config('app.name'),
            'tagline' => 'Code, scripts & digital assets',
        ]);
        return view('admin.settings.edit', compact('hero', 'site'));
    }

    public function update(Request $request)
    {
        SiteSetting::set('homepage.hero', [
            'title' => $request->input('hero_title'),
            'subtitle' => $request->input('hero_subtitle'),
        ], 'homepage');
        SiteSetting::set('site', [
            'name' => $request->input('site_name'),
            'tagline' => $request->input('site_tagline'),
        ], 'site');
        return back()->with('success', 'Settings saved.');
    }
}
