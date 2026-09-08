<?php

namespace App\Http\Middleware;

use App\Support\ProductActivation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProductActivated
{
    public function handle(Request $request, Closure $next): Response
    {
        // Licensing can be fully disabled for author / white-label builds.
        if (config('services.envato.licensing_disabled') || env('DISABLE_PRODUCT_LICENSE', false)) {
            return $next($request);
        }

        if (ProductActivation::isActive()) {
            return $next($request);
        }

        $name = optional($request->route())->getName();

        if (ProductActivation::routeIsAllowed($name)) {
            return $next($request);
        }

        return redirect()
            ->route('admin.activation.edit')
            ->with('error', 'Activate your license to unlock this feature. You can still use Basic Settings and Blog.');
    }
}
