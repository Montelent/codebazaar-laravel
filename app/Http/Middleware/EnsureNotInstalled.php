<?php

namespace App\Http\Middleware;

use App\Support\Installer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Installer::isInstalled()) {
            // Don't 404 — that looks like a broken server. Explain clearly.
            return redirect('/')->with(
                'error',
                'This site is already installed. To reinstall: delete the file storage/installed, then open /install again.'
            );
        }

        return $next($request);
    }
}
