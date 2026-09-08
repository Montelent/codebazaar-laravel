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
            /*
             | Security: do not confirm that an installer exists, and never
             | publish reinstall steps on a public URL. Attackers probing
             | /install should see the same response as any unknown path.
             */
            abort(404);
        }

        return $next($request);
    }
}
