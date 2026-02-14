@extends('layouts.app')

@section('title', 'Companies - StartupGraph')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Companies</h1>
            <p class="text-gray-600">{{ $companies->total() }} startups tracked</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('companies.export.csv', request()->query()) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                Export CSV
            </a>
            <a href="{{ route('companies.export.json', request()->query()) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                Export JSON
            </a>
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
                    name="category"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Categories</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
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
                    name="funded_recent"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Last Fundraise</option>
                    <option value="3m" {{ request('funded_recent') == '3m' ? 'selected' : '' }}>Past 3 months</option>
                    <option value="6m" {{ request('funded_recent') == '6m' ? 'selected' : '' }}>Past 6 months</option>
                    <option value="1y" {{ request('funded_recent') == '1y' ? 'selected' : '' }}>Past year</option>
                    <option value="2y" {{ request('funded_recent') == '2y' ? 'selected' : '' }}>Past 2 years</option>
                </select>
            </div>
            <button
                type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                Filter
            </button>
            @if(request()->hasAny(['search', 'category', 'country', 'funded_recent', 'funded_after', 'funded_before']))
                <a
                    href="{{ route('companies.index') }}"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-center"
                >
                    Clear
                </a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') !== 'desc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">
                                Company
                                @if(request('sort', 'name') === 'name')
                                    <span class="ml-1">{{ request('direction') === 'desc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'category', 'direction' => request('sort') === 'category' && request('direction') !== 'desc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">
                                Category
                                @if(request('sort') === 'category')
                                    <span class="ml-1">{{ request('direction') === 'desc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'city', 'direction' => request('sort') === 'city' && request('direction') !== 'desc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">
                                Location
                                @if(request('sort') === 'city')
                                    <span class="ml-1">{{ request('direction') === 'desc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'founded_date', 'direction' => request('sort') === 'founded_date' && request('direction') !== 'desc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">
                                Founded
                                @if(request('sort') === 'founded_date')
                                    <span class="ml-1">{{ request('direction') === 'desc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'funding_rounds_sum_amount', 'direction' => request('sort') === 'funding_rounds_sum_amount' && request('direction') !== 'desc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">
                                Total Raised
                                @if(request('sort') === 'funding_rounds_sum_amount')
                                    <span class="ml-1">{{ request('direction') === 'desc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest_funding_date', 'direction' => request('sort') === 'latest_funding_date' && request('direction') !== 'desc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">
                                Last Fundraise
                                @if(request('sort') === 'latest_funding_date')
                                    <span class="ml-1">{{ request('direction') === 'desc' ? '↓' : '↑' }}</span>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employees
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Website
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($companies as $company)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('companies.show', $company) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('companies.show', $company) }}" class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                    {{ $company->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($company->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $company->category_label }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ collect([$company->city, $company->country])->filter()->join(', ') ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $company->founded_date?->format('Y') ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                @if($company->funding_rounds_sum_amount)
                                    @if($company->funding_rounds_sum_amount >= 1000000000)
                                        ${{ number_format($company->funding_rounds_sum_amount / 1000000000, 1) }}B
                                    @else
                                        ${{ number_format($company->funding_rounds_sum_amount / 1000000, 0) }}M
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($company->latestFundingRound)
                                    @if($company->latestFundingRound->source_url)
                                        <a href="{{ $company->latestFundingRound->source_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline" onclick="event.stopPropagation()">
                                            {{ $company->latestFundingRound->announced_date?->format('M Y') }}
                                        </a>
                                    @else
                                        {{ $company->latestFundingRound->announced_date?->format('M Y') ?: '—' }}
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ $company->current_headcount ? number_format($company->current_headcount) : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($company->website)
                                    <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline" onclick="event.stopPropagation()">
                                        {{ parse_url($company->website, PHP_URL_HOST) }}
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                No companies found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $companies->links() }}
    </div>
</div>
@endsection
