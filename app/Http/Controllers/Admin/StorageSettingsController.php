<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;

class StorageSettingsController extends Controller
{
    public function edit()
    {
        $config = MediaStorageService::config();
        $disks = MediaStorageService::disks();

        return view('admin.settings.storage', compact('config', 'disks'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'default_disk' => 'required|in:local,s3,backblaze,idrive',
            's3' => 'nullable|array',
            'backblaze' => 'nullable|array',
            'idrive' => 'nullable|array',
        ]);

        $current = MediaStorageService::config();

        foreach (['s3', 'backblaze', 'idrive'] as $provider) {
            $incoming = $data[$provider] ?? [];
            $merged = array_merge($current[$provider] ?? [], $incoming);
            // Keep existing secret if left blank
            if (($incoming['secret'] ?? '') === '' && ! empty($current[$provider]['secret'])) {
                $merged['secret'] = $current[$provider]['secret'];
            }
            $merged['path_style'] = ! empty($incoming['path_style']);
            $data[$provider] = $merged;
        }

        SiteSetting::setValue('file_storage', [
            'default_disk' => $data['default_disk'],
            's3' => $data['s3'],
            'backblaze' => $data['backblaze'],
            'idrive' => $data['idrive'],
        ], 'storage');

        return back()->with('success', 'File storage settings saved.');
    }
}
