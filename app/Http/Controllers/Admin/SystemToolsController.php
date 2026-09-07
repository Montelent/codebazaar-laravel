<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

/**
 * Browser-based maintenance tools for shared hosting (no SSH required).
 */
class SystemToolsController extends Controller
{
    public function index()
    {
        return view('admin.tools.index');
    }

    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output()) ?: 'No pending migrations.';

            return back()->with('success', "Migrations completed.\n{$output}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Migration failed: '.$e->getMessage());
        }
    }

    public function clearCache()
    {
        try {
            $commands = [
                'optimize:clear',
                'config:clear',
                'route:clear',
                'view:clear',
                'event:clear',
                'cache:clear',
            ];

            $lines = [];
            foreach ($commands as $cmd) {
                try {
                    Artisan::call($cmd);
                    $out = trim(Artisan::output());
                    $lines[] = $cmd.($out ? " → {$out}" : ' → OK');
                } catch (\Throwable $e) {
                    $lines[] = $cmd.' → '.$e->getMessage();
                }
            }

            return back()->with('success', "Caches cleared.\n".implode("\n", $lines));
        } catch (\Throwable $e) {
            return back()->with('error', 'Cache clear failed: '.$e->getMessage());
        }
    }
}
