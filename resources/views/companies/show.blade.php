@extends('layouts.app')

@section('title', $company->name . ' - StartupGraph')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('companies.index') }}" class="text-blue-600 hover:underline">Companies</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-600">{{ $company->name }}</span>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $company->name }}</h1>
                @if($company->city || $company->country)
                    <p class="text-gray-500 mt-1">
                        {{ collect([$company->city, $company->state, $company->country])->filter()->join(', ') }}
                    </p>
                @endif
            </div>

            @if($company->website)
                <a
                    href="{{ $company->website }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Visit Website
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            @endif
        </div>

        @if($company->description)
            <p class="text-gray-600 mt-6 leading-relaxed">{{ $company->description }}</p>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-8 pt-6 border-t">
            @if($company->founded_date)
                <div>
                    <p class="text-sm text-gray-500">Founded</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $company->founded_date->format('M Y') }}</p>
                </div>
            @endif
            @if($company->current_headcount)
                <div>
                    <p class="text-sm text-gray-500">Employees</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($company->current_headcount) }}</p>
                </div>
            @endif
            @if($company->fundingRounds->count())
                <div>
                    <p class="text-sm text-gray-500">Funding Rounds</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $company->fundingRounds->count() }}</p>
                </div>
            @endif
            @if($company->fundingRounds->sum('amount'))
                @php $totalRaised = $company->fundingRounds->sum('amount'); @endphp
                <div>
                    <p class="text-sm text-gray-500">Total Raised</p>
                    <p class="text-lg font-semibold text-gray-900">
                        @if($totalRaised >= 1000000000)
                            ${{ number_format($totalRaised / 1000000000, 1) }}B
                        @else
                            ${{ number_format($totalRaised / 1000000, 0) }}M
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    @if($company->product_highlights && count($company->product_highlights))
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">About the Product</h2>
            <ul class="space-y-2">
                @foreach($company->product_highlights as $highlight)
                    <li class="flex items-start gap-2">
                        <span class="text-blue-500 mt-1">&#x2022;</span>
                        <span class="text-gray-700">{{ $highlight }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($company->people->count())
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Leadership</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($company->people->where('pivot.is_current', true) as $person)
                    <a href="{{ route('people.show', $person) }}" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        @if($person->photo_url)
                            <img src="{{ $person->photo_url }}" alt="{{ $person->name }}" class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold">
                                {{ collect(explode(' ', $person->name))->map(fn($n) => substr($n, 0, 1))->join('') }}
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-gray-900 hover:text-blue-600">{{ $person->name }}</p>
                            @if($person->pivot->role)
                                <p class="text-sm text-gray-500">{{ $person->pivot->role }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($company->fundingRounds->count())
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Funding History</h2>
            <div class="space-y-4">
                @foreach($company->fundingRounds->sortByDesc('announced_date') as $round)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ ucfirst(str_replace('_', ' ', $round->round_type ?? 'Funding Round')) }}
                            </p>
                            @if($round->investors->count())
                                <p class="text-sm text-gray-500">
                                    {{ $round->investors->pluck('name')->join(', ') }}
                                </p>
                            @endif
                        </div>
                        <div class="text-right mt-2 sm:mt-0">
                            @if($round->amount)
                                <p class="font-semibold text-gray-900">
                                    @if($round->amount >= 1000000000)
                                        ${{ number_format($round->amount / 1000000000, 1) }}B
                                    @else
                                        ${{ number_format($round->amount / 1000000, 0) }}M
                                    @endif
                                </p>
                            @endif
                            @if($round->announced_date)
                                <p class="text-sm text-gray-500">{{ $round->announced_date->format('M Y') }}</p>
                            @endif
                            @if($round->source_url)
                                <a href="{{ $round->source_url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-600 hover:underline">
                                    Source
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($company->headcountSnapshots->count())
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Employee Growth</h2>
            @if($company->headcountSnapshots->count() >= 2)
                <div class="h-64">
                    <canvas id="headcountChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">Current</span>
                    <span class="font-semibold text-gray-900">{{ number_format($company->headcountSnapshots->first()->headcount) }} employees</span>
                </div>
                <p class="text-sm text-gray-500 mt-3">More data points needed to show growth chart.</p>
            @endif
        </div>
    @endif

    @if($company->newsMentions->count())
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">News & Press</h2>
            <div class="space-y-4">
                @foreach($company->newsMentions->sortByDesc('published_date') as $news)
                    <a href="{{ $news->url }}" target="_blank" rel="noopener noreferrer" class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <p class="font-semibold text-gray-900">{{ $news->title }}</p>
                        <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                            @if($news->source)
                                <span>{{ $news->source }}</span>
                            @endif
                            @if($news->published_date)
                                <span>&middot;</span>
                                <span>{{ $news->published_date->format('M d, Y') }}</span>
                            @endif
                        </div>
                        @if($news->summary)
                            <p class="text-gray-600 mt-2 text-sm">{{ $news->summary }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if(!$company->fundingRounds->count() && !$company->headcountSnapshots->count() && !$company->newsMentions->count())
        <div class="bg-white rounded-lg shadow-sm border p-6 text-center">
            <p class="text-gray-500">No additional data available for this company yet.</p>
        </div>
    @endif
</div>

@if($company->headcountSnapshots->count() >= 2)
@push('head')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('headcountChart').getContext('2d');

    const snapshots = @json($company->headcountSnapshots->sortBy('recorded_date')->values(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    const labels = snapshots.map(s => {
        const date = new Date(s.recorded_date);
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });

    const data = snapshots.map(s => s.headcount);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Employees',
                data: data,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toLocaleString() + ' employees';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endif
@endsection
