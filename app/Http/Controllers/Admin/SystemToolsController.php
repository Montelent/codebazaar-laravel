<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Browser-based maintenance tools for shared hosting (no SSH required).
 *
 * Hostinger / LiteSpeed often returns 405 when POST is redirected (trailing slash).
 * Actions therefore accept GET with a CSRF token as well as POST.
 */
class SystemToolsController extends Controller
{
    public function index()
    {
        return view('admin.tools.index');
    }

    public function run(Request $request, string $action)
    {
        // Accept POST body token or query-string token (Hostinger-safe GET links)
        $token = $request->input('_token') ?: $request->query('_token');
        if (! $token || ! hash_equals((string) $request->session()->token(), (string) $token)) {
            return redirect()
                ->route('admin.tools.index')
                ->with('error', 'Invalid or missing security token. Open System tools and try again.');
        }

        return match ($action) {
            'migrate' => $this->migrate(),
            'clear-cache' => $this->clearCache(),
            default => redirect()->route('admin.tools.index')->with('error', 'Unknown action.'),
        };
    }

    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output()) ?: 'No pending migrations.';

            return redirect()
                ->route('admin.tools.index')
                ->with('success', "Migrations completed.\n{$output}");
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.tools.index')
                ->with('error', 'Migration failed: '.$e->getMessage());
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

            return redirect()
                ->route('admin.tools.index')
                ->with('success', "Caches cleared.\n".implode("\n", $lines));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.tools.index')
                ->with('error', 'Cache clear failed: '.$e->getMessage());
        }
    }
}
