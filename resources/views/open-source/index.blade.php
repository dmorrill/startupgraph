@extends('layouts.app')

@section('title', 'Open Source Projects - StartupGraph')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Open Source Projects</h1>
        <p class="text-gray-600">{{ $projects->total() }} open-source projects tracked, ranked by GitHub stars</p>
    </div>

    <form method="GET" action="{{ route('open-source.index') }}" class="bg-white p-4 rounded-lg shadow-sm border">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search projects..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="language" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Languages</option>
                    @foreach($languages as $lang)
                        <option value="{{ $lang }}" {{ request('language') === $lang ? 'selected' : '' }}>{{ $lang }}</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="self_hostable" value="1" {{ request()->boolean('self_hostable') ? 'checked' : '' }} class="rounded border-gray-300">
                Self-hostable only
            </label>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
        </div>
    </form>

    <div class="grid gap-4">
        @forelse($projects as $project)
        <div class="bg-white rounded-lg shadow-sm border p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-gray-900">
                            <a href="{{ $project->github_url }}" target="_blank" class="hover:text-blue-600">{{ $project->name }}</a>
                        </h2>
                        @if($project->self_hostable)
                            <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full font-medium">Self-hostable</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $project->github_owner }}/{{ $project->github_repo }}</p>
                    @if($project->description)
                        <p class="text-gray-700 mt-2 line-clamp-2">{{ $project->description }}</p>
                    @endif
                    @if($project->topics)
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach(array_slice($project->topics, 0, 6) as $topic)
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">{{ $topic }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-4 ml-4 text-sm text-gray-500 shrink-0">
                    <div class="text-right">
                        <p class="font-mono font-semibold text-gray-900">{{ number_format($project->stars) }}</p>
                        <p class="text-xs">stars</p>
                    </div>
                    <div class="text-right">
                        <p class="font-mono">{{ number_format($project->forks) }}</p>
                        <p class="text-xs">forks</p>
                    </div>
                    @if($project->primary_language)
                        <div class="text-right">
                            <p class="font-medium text-gray-700">{{ $project->primary_language }}</p>
                            <p class="text-xs">language</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow-sm border p-12 text-center text-gray-500">
            <p class="text-lg">No open-source projects found.</p>
            <p class="text-sm mt-1">Check back soon — we're importing projects from GitHub.</p>
        </div>
        @endforelse
    </div>

    <div>{{ $projects->links() }}</div>
</div>
@endsection
