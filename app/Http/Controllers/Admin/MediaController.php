<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index()
    {
        $assets = Schema::hasTable('media_assets')
            ? MediaAsset::orderByDesc('id')->paginate(40)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 40);

        return view('admin.media.index', compact('assets'));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('media_assets')) {
            return back()->with('error', 'Run DB migrations first.');
        }

        // External URL option
        if ($request->filled('external_url')) {
            $url = $request->validate(['external_url' => 'required|url|max:1000', 'alt' => 'nullable|string|max:200']);
            $asset = MediaAsset::create([
                'user_id' => $request->user()->id,
                'url' => $url['external_url'],
                'filename' => basename(parse_url($url['external_url'], PHP_URL_PATH) ?: 'external'),
                'mime_type' => null,
                'size' => null,
                'alt' => $url['alt'] ?? null,
                'disk' => 'external',
                'path' => null,
            ]);

            return back()->with('success', 'External media added. URL: '.$asset->url);
        }

        $request->validate([
            'file' => 'required|file|max:20480|mimes:jpg,jpeg,png,gif,webp,svg,pdf,zip',
            'alt' => 'nullable|string|max:200',
        ]);

        $file = $request->file('file');
        $dir = 'media/'.date('Y/m');
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'-'.Str::random(6).'.'.$file->getClientOriginalExtension();

        // Ensure public disk root exists
        $publicRoot = storage_path('app/public');
        if (! is_dir($publicRoot)) {
            @mkdir($publicRoot, 0755, true);
        }
        if (! is_dir($publicRoot.'/media')) {
            @mkdir($publicRoot.'/media', 0755, true);
        }

        $path = $file->storeAs($dir, $name, 'public');

        // Public URL: /storage/... requires storage:link or we serve via route
        $url = url('/media/file/'.$path);

        $asset = MediaAsset::create([
            'user_id' => $request->user()->id,
            'url' => $url,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt' => $request->input('alt'),
            'disk' => 'public',
            'path' => $path,
        ]);

        return back()->with('success', 'Uploaded. Copy URL: '.$asset->url);
    }

    public function destroy(MediaAsset $medium)
    {
        if ($medium->disk === 'public' && $medium->path) {
            Storage::disk('public')->delete($medium->path);
        }
        $medium->delete();

        return back()->with('success', 'Media deleted.');
    }

    /** Serve stored files without needing php artisan storage:link */
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
