<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index()
    {
        $assets = Schema::hasTable('media_assets')
            ? MediaAsset::orderByDesc('id')->paginate(40)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40);

        $config = MediaStorageService::config();
        $disks = MediaStorageService::disks();

        return view('admin.media.index', compact('assets', 'config', 'disks'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('media_assets')) {
            return back()->with('error', 'Run DB migrations first (System tools).');
        }

        $service = new MediaStorageService;

        // External / Drive URL
        if ($request->filled('external_url')) {
            $data = $request->validate([
                'external_url' => 'required|string|max:2000',
                'alt' => 'nullable|string|max:200',
                'disk' => 'nullable|in:external,drive',
            ]);
            $disk = $data['disk'] ?? 'external';
            $asset = $service->storeExternalUrl($data['external_url'], $disk, $request->user()->id, $data['alt'] ?? null);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => true, 'asset' => $asset]);
            }

            return back()->with('success', 'Media URL added: '.$asset->url);
        }

        $data = $request->validate([
            'file' => 'required|file|max:51200',
            'alt' => 'nullable|string|max:200',
            'disk' => 'nullable|in:local,s3,backblaze,idrive',
        ]);

        try {
            $disk = $data['disk'] ?: (MediaStorageService::config()['default_disk'] ?? 'local');
            $asset = $service->storeUpload($request->file('file'), $disk, $request->user()->id, $data['alt'] ?? null);
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'asset' => $asset]);
        }

        return back()->with('success', 'Uploaded ('.$asset->disk.'). URL: '.$asset->url);
    }

    public function destroy(MediaAsset $medium)
    {
        if ($medium->disk === 'public' && $medium->path) {
            Storage::disk('public')->delete($medium->path);
        }
        $medium->delete();

        return back()->with('success', 'Media deleted.');
    }

    /** JSON list for product form media picker */
    public function json(Request $request)
    {
        if (! Schema::hasTable('media_assets')) {
            return response()->json(['data' => []]);
        }

        $q = MediaAsset::orderByDesc('id');
        if ($request->filled('images')) {
            $q->where(function ($w) {
                $w->where('mime_type', 'like', 'image/%')
                    ->orWhere('url', 'like', '%.jpg%')
                    ->orWhere('url', 'like', '%.jpeg%')
                    ->orWhere('url', 'like', '%.png%')
                    ->orWhere('url', 'like', '%.webp%')
                    ->orWhere('url', 'like', '%.gif%');
            });
        }

        return response()->json(['data' => $q->limit(60)->get()]);
    }

    public function serve(string $path)
    {
        $path = str_replace('..', '', $path);
        $full = storage_path('app/public/'.$path);
        if (! is_file($full)) {
            abort(404);
        }

        return response()->file($full);
    }
}
