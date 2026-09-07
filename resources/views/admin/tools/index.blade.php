@extends('layouts.admin')
@section('title', 'System tools')
@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold">Database migrations</h2>
        <p class="mt-1 text-sm text-slate-600">
            Run any pending Laravel migrations. Safe to click more than once — already-run migrations are skipped.
        </p>
        <form method="post" action="{{ route('admin.tools.migrate') }}" class="mt-4">
            @csrf
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Run DB migrations
            </button>
        </form>
    </div>

    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold">Clear Laravel caches</h2>
        <p class="mt-1 text-sm text-slate-600">
            Clears application, config, route, view, and event caches (same as
            <code class="rounded bg-slate-100 px-1 text-xs">php artisan optimize:clear</code>).
            Use after updating code, .env, or Blade views if the site looks stale.
        </p>
        <form method="post" action="{{ route('admin.tools.clear-cache') }}" class="mt-4">
            @csrf
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                Clear all caches
            </button>
        </form>
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-950">
        <h2 class="font-semibold">Cron / scheduled tasks</h2>
        <p class="mt-2">
            Laravel needs <strong>one</strong> cron entry that runs every minute. On Hostinger (hPanel → Advanced → Cron Jobs):
        </p>
        <pre class="mt-3 overflow-x-auto rounded-lg bg-white/80 p-3 text-xs leading-relaxed">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&amp;1</pre>
        <p class="mt-3 text-amber-900/80">
            Or use the absolute PHP binary if the host requires it, e.g.
            <code class="rounded bg-white px-1 text-xs">/usr/bin/php</code>.
            See <strong>HOSTINGER.md</strong> and <strong>Documentation.md</strong> for details.
        </p>
    </div>
</div>
@endsection
