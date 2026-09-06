@extends('layouts.admin')
@section('title', 'Blog')
@section('content')
<div class="mb-4 flex justify-between"><h1 class="text-xl font-bold">Blog posts</h1>
<a href="{{ route('admin.blog.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">New post</a></div>
<div class="overflow-hidden rounded-xl border bg-white">
<table class="w-full text-sm"><thead class="border-b bg-slate-50"><tr><th class="px-4 py-3">Title</th><th>Status</th><th>Category</th><th></th></tr></thead>
<tbody>
@foreach($posts as $p)
<tr class="border-b"><td class="px-4 py-3">{{ $p->title }}</td><td>{{ $p->status }}</td><td>{{ $p->category }}</td>
<td class="px-4 text-right"><a href="{{ route('admin.blog.edit', $p) }}" class="text-emerald-700">Edit</a></td></tr>
@endforeach
</tbody></table></div>
{{ $posts->links() }}
@endsection
