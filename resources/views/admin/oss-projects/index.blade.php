@extends('admin.layouts.app')

@section('title', 'OSS Projects - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Open Source Projects</h1>
            <p class="text-gray-600">{{ $projects->total() }} projects tracked</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.oss-projects.index') }}" class="bg-white p-4 rounded-lg shadow-sm border">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search projects..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select name="category" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Language</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">⭐ Stars</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Forks</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">License</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Commit</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($projects as $project)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div>
                            <a href="{{ route('admin.oss-projects.show', $project) }}" class="font-medium text-blue-600 hover:text-blue-800">{{ $project->name }}</a>
                            <p class="text-sm text-gray-500 truncate max-w-md">{{ $project->description }}</p>
                            <p class="text-xs text-gray-400">{{ $project->github_owner }}/{{ $project->github_repo }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $project->primary_language ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-right font-mono">{{ number_format($project->stars) }}</td>
                    <td class="px-6 py-4 text-sm text-right font-mono">{{ number_format($project->forks) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $project->license ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $project->last_commit_at?->diffForHumans() ?? '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ $project->github_url }}" target="_blank" class="text-gray-400 hover:text-gray-600">GitHub ↗</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">No projects found. Run <code>php artisan app:discover-oss-projects</code> to import.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $projects->links() }}</div>
</div>
@endsection
