@extends('install.layout')
@section('title', 'Database')
@section('content')
<h1 class="text-xl font-bold">2. Site & database</h1>
<form method="post" action="{{ route('install.database.store') }}" class="mt-6 space-y-4">
    @csrf
    <div>
        <label class="text-sm font-medium">Site name</label>
        <input name="app_name" value="{{ old('app_name', 'CodeBazaar') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Site URL</label>
        <input type="url" name="app_url" value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="https://yoursite.com">
    </div>
    <div>
        <label class="text-sm font-medium">Database driver</label>
        <select name="db_connection" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
            <option value="mysql" @selected(old('db_connection', 'mysql') === 'mysql')>MySQL / MariaDB</option>
            <option value="pgsql" @selected(old('db_connection') === 'pgsql')>PostgreSQL</option>
            <option value="sqlite" @selected(old('db_connection') === 'sqlite')>SQLite</option>
        </select>
    </div>
    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="text-sm font-medium">DB host</label>
            <input name="db_host" value="{{ old('db_host', '127.0.0.1') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">DB port</label>
            <input name="db_port" value="{{ old('db_port', '3306') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        </div>
    </div>
    <div>
        <label class="text-sm font-medium">Database name</label>
        <input name="db_database" value="{{ old('db_database') }}" required class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" placeholder="codebazaar">
        <p class="mt-1 text-xs text-slate-500">For SQLite use a filename e.g. database.sqlite</p>
    </div>
    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="text-sm font-medium">DB username</label>
            <input name="db_username" value="{{ old('db_username') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium">DB password</label>
            <input type="password" name="db_password" value="{{ old('db_password') }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
        </div>
    </div>
    <div class="flex justify-between pt-2">
        <a href="{{ route('install.requirements') }}" class="text-sm text-slate-500 hover:underline">← Back</a>
        <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Test & continue →</button>
    </div>
</form>
@endsection
