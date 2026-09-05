@extends('install.layout')
@section('title', 'Complete')
@section('content')
<div class="text-center">
    <div class="text-4xl">✓</div>
    <h1 class="mt-2 text-xl font-bold text-emerald-800">Installation complete</h1>
    <p class="mt-2 text-sm text-slate-600">CodeBazaar is ready. The installer URL is now disabled.</p>
    <div class="mt-6 rounded-lg bg-slate-50 p-4 text-left text-sm">
        <p><strong>Admin email:</strong> {{ $email }}</p>
        <p class="mt-1"><strong>Login:</strong> <a class="text-emerald-700 underline" href="{{ rtrim($url, '/') }}/login">{{ rtrim($url, '/') }}/login</a></p>
        <p class="mt-1"><strong>Admin:</strong> <a class="text-emerald-700 underline" href="{{ rtrim($url, '/') }}/admin">{{ rtrim($url, '/') }}/admin</a></p>
    </div>
    <a href="{{ rtrim($url, '/') }}/" class="mt-6 inline-block rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">Go to storefront</a>
</div>
@endsection
