@extends('layouts.app')

@section('title', 'Dashboard — StartupGraph')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Welcome card for new users --}}
    @if($isNewUser)
    <div class="bg-gray-800 text-white rounded-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-2">Welcome to StartupGraph! 🚀</h2>
        <p class="text-gray-300 mb-4">You now have access to {{ number_format($companyCount) }} companies. Here's what you can do:</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <div class="flex items-start space-x-2">
                <span class="text-green-400">✓</span>
                <span><strong>Search & filter</strong> companies by category, location, and funding</span>
            </div>
            <div class="flex items-start space-x-2">
                <span class="text-green-400">✓</span>
                <span><strong>Save searches</strong> to quickly revisit your filters</span>
            </div>
            <div class="flex items-start space-x-2">
                <span class="text-green-400">✓</span>
                <span><strong>Follow companies</strong> to build your watchlist</span>
            </div>
            <div class="flex items-start space-x-2">
                <span class="text-green-400">✓</span>
                <span><strong>Share feedback</strong> — we're building this for you</span>
            </div>
        </div>
        <a href="{{ route('companies.index') }}" class="inline-block mt-4 px-4 py-2 bg-white text-gray-800 text-sm font-medium rounded-md hover:bg-gray-100">
            Start exploring →
        </a>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Followed Companies --}}
        <div class="lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Followed Companies</h2>
            @if($followedCompanies->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($followedCompanies as $company)
                    <a href="{{ route('companies.show', $company) }}" class="block bg-white border rounded-lg p-4 hover:shadow-sm transition">
                        <div class="font-medium text-gray-900">{{ $company->name }}</div>
                        @if($company->category)
                        <span class="inline-block mt-1 px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded">{{ $company->category }}</span>
                        @endif
                        @if($company->city || $company->country)
                        <div class="text-xs text-gray-500 mt-1">{{ collect([$company->city, $company->state, $company->country])->filter()->implode(', ') }}</div>
                        @endif
                    </a>
                    @endforeach
                </div>
            @else
                <div class="bg-white border rounded-lg p-6 text-center text-gray-500">
                    <p>No followed companies yet.</p>
                    <a href="{{ route('companies.index') }}" class="text-gray-800 underline text-sm mt-2 inline-block">Browse companies →</a>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-8">
            {{-- Saved Searches --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Saved Searches</h2>
                @if($savedSearches->count())
                    <div class="space-y-2">
                        @foreach($savedSearches as $search)
                        <a href="{{ route('companies.index', $search->filters ?? []) }}" class="block bg-white border rounded-lg p-3 hover:shadow-sm transition">
                            <div class="font-medium text-gray-900 text-sm">{{ $search->name }}</div>
                        </a>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white border rounded-lg p-4 text-center text-gray-500 text-sm">
                        <p>No saved searches yet.</p>
                        <a href="{{ route('companies.index') }}" class="text-gray-800 underline mt-1 inline-block">Search companies →</a>
                    </div>
                @endif
            </div>

            {{-- Recently Viewed --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Recently Viewed</h2>
                @if($recentlyViewed->count())
                    <div class="space-y-2">
                        @foreach($recentlyViewed as $company)
                        <a href="{{ route('companies.show', $company) }}" class="block bg-white border rounded-lg p-3 hover:shadow-sm transition">
                            <div class="font-medium text-gray-900 text-sm">{{ $company->name }}</div>
                        </a>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white border rounded-lg p-4 text-center text-gray-500 text-sm">
                        <p>No views yet — start browsing!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
