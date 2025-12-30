@extends('layouts.app')

@section('title', 'Companies - StartupGraph')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Companies</h1>
            <p class="text-gray-600">{{ $companies->total() }} startups tracked</p>
        </div>
    </div>

    <form method="GET" action="{{ route('companies.index') }}" class="bg-white p-4 rounded-lg shadow-sm border">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search companies..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
            <div class="sm:w-48">
                <select
                    name="country"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Countries</option>
                    @foreach($countries as $country)
                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                            {{ $country }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-48">
                <select
                    name="sort"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="name" {{ request('sort', 'name') == 'name' ? 'selected' : '' }}>Sort by Name</option>
                    <option value="founded_date" {{ request('sort') == 'founded_date' ? 'selected' : '' }}>Sort by Founded</option>
                    <option value="city" {{ request('sort') == 'city' ? 'selected' : '' }}>Sort by City</option>
                </select>
            </div>
            <button
                type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                Filter
            </button>
            @if(request()->hasAny(['search', 'country', 'sort']))
                <a
                    href="{{ route('companies.index') }}"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-center"
                >
                    Clear
                </a>
            @endif
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($companies as $company)
            <a href="{{ route('companies.show', $company) }}" class="block">
                <div class="bg-white rounded-lg shadow-sm border p-6 hover:shadow-md transition-shadow h-full">
                    <div class="flex items-start justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">{{ $company->name }}</h2>
                        @if($company->founded_date)
                            <span class="text-sm text-gray-500">{{ $company->founded_date->format('Y') }}</span>
                        @endif
                    </div>

                    @if($company->city || $company->country)
                        <p class="text-sm text-gray-500 mt-1">
                            {{ collect([$company->city, $company->state, $company->country])->filter()->join(', ') }}
                        </p>
                    @endif

                    @if($company->description)
                        <p class="text-gray-600 mt-3 text-sm line-clamp-3">
                            {{ Str::limit($company->description, 150) }}
                        </p>
                    @endif

                    @if($company->website)
                        <p class="text-blue-600 text-sm mt-3 truncate">
                            {{ parse_url($company->website, PHP_URL_HOST) }}
                        </p>
                    @endif
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500">No companies found matching your criteria.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $companies->links() }}
    </div>
</div>
@endsection
