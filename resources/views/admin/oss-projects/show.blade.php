@extends('admin.layouts.app')

@section('title', $ossProject->name . ' - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $ossProject->name }}</h1>
            <p class="text-gray-500">{{ $ossProject->github_owner }}/{{ $ossProject->github_repo }}</p>
        </div>
        <a href="{{ $ossProject->github_url }}" target="_blank" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">View on GitHub ↗</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-gray-500">Stars</p>
                <p class="text-2xl font-bold">{{ number_format($ossProject->stars) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Forks</p>
                <p class="text-2xl font-bold">{{ number_format($ossProject->forks) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Language</p>
                <p class="text-2xl font-bold">{{ $ossProject->primary_language ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">License</p>
                <p class="text-2xl font-bold">{{ $ossProject->license ?? '—' }}</p>
            </div>
        </div>
        @if($ossProject->description)
            <p class="mt-4 text-gray-700">{{ $ossProject->description }}</p>
        @endif
        @if($ossProject->topics)
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($ossProject->topics as $topic)
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ $topic }}</span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Linked Companies --}}
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h2 class="text-lg font-semibold mb-4">Linked Companies</h2>

        @if($ossProject->companies->count())
            <div class="space-y-2 mb-6">
                @foreach($ossProject->companies as $company)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="font-medium">{{ $company->name }}</span>
                            <span class="ml-2 px-2 py-0.5 bg-gray-200 text-gray-700 text-xs rounded">{{ str_replace('_', ' ', $company->pivot->relationship_type) }}</span>
                            @if($company->pivot->notes)
                                <span class="ml-2 text-sm text-gray-500">{{ $company->pivot->notes }}</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.oss-projects.unlink-company', [$ossProject, $company]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Unlink</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 mb-4">No companies linked yet.</p>
        @endif

        <form method="POST" action="{{ route('admin.oss-projects.link-company', $ossProject) }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <select name="company_id" required class="rounded-md border-gray-300 shadow-sm flex-1">
                <option value="">Select a company...</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
            <select name="relationship_type" required class="rounded-md border-gray-300 shadow-sm">
                <option value="alternative_to">Alternative to</option>
                <option value="fork_of">Fork of</option>
                <option value="built_on">Built on</option>
                <option value="commercial_version_of">Commercial version of</option>
            </select>
            <input type="text" name="notes" placeholder="Notes (optional)" class="rounded-md border-gray-300 shadow-sm">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 whitespace-nowrap">Link Company</button>
        </form>
    </div>
</div>
@endsection
