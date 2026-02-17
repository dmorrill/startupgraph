@extends('layouts.app')

@section('title', 'Saved Searches - StartupGraph')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Saved Searches & Watchlists</h1>
        <p class="text-gray-600">Your saved search queries. We'll notify you when new companies match.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($savedSearches->count())
        <div class="space-y-4">
            @foreach($savedSearches as $search)
                <div class="bg-white rounded-lg shadow-sm border p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <a href="{{ $search->search_url }}" class="font-semibold text-blue-600 hover:underline text-lg">
                            {{ $search->display_name }}
                        </a>
                        <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                            @if($search->notify_on_new)
                                <span class="inline-flex items-center gap-1 text-green-600">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/><path d="M10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
                                    Notifications on
                                </span>
                            @else
                                <span class="text-gray-400">Notifications off</span>
                            @endif
                            <span>Saved {{ $search->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ $search->search_url }}" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md text-sm hover:bg-blue-100">
                            Run Search
                        </a>
                        <form method="POST" action="{{ route('saved-searches.update', $search) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="notify_on_new" value="{{ $search->notify_on_new ? '0' : '1' }}">
                            <button type="submit" class="px-3 py-1.5 bg-gray-50 text-gray-700 rounded-md text-sm hover:bg-gray-100">
                                {{ $search->notify_on_new ? 'Mute' : 'Notify' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('saved-searches.destroy', $search) }}" onsubmit="return confirm('Remove this saved search?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-700 rounded-md text-sm hover:bg-red-100">
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
            <p class="text-gray-500 mb-4">No saved searches yet.</p>
            <a href="{{ route('companies.index') }}" class="text-blue-600 hover:underline">Search companies</a> and click "Watch this search" to save it.
        </div>
    @endif
</div>
@endsection
