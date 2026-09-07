<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\AdSlots;
use Illuminate\Http\Request;

class AdSettingsController extends Controller
{
    public function edit()
    {
        $config = AdSlots::config();
        $definitions = AdSlots::definitions();

        return view('admin.settings.ads', compact('config', 'definitions'));
    }

    public function update(Request $request)
    {
        $slots = [];
        foreach (array_keys(AdSlots::definitions()) as $key) {
            $slots[$key] = [
                'enabled' => $request->boolean('slots.'.$key.'.enabled'),
                'html' => (string) $request->input('slots.'.$key.'.html', ''),
            ];
        }

        SiteSetting::setValue('ad_placements', [
            'enabled' => $request->boolean('enabled'),
            'middle_paragraph' => max(1, (int) $request->input('middle_paragraph', 3)),
            'slots' => $slots,
        ], 'ads');

        return back()->with('success', 'Ad placements saved.');
    }
}
