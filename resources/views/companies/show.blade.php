@extends('layouts.app')

@section('title', $company->name . ' - StartupGraph')

@push('head')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": @json($company->name),
    "url": @json($company->website),
    @if($company->description)
    "description": @json($company->description),
    @endif
    @if($company->founded_date)
    "foundingDate": "{{ $company->founded_date->format('Y-m-d') }}",
    @endif
    @if($company->city || $company->country)
    "address": {
        "@type": "PostalAddress"
        @if($company->city)
        ,"addressLocality": @json($company->city)
        @endif
        @if($company->state)
        ,"addressRegion": @json($company->state)
        @endif
        @if($company->country)
        ,"addressCountry": @json($company->country)
        @endif
    },
    @endif
    @if($company->current_headcount)
    "numberOfEmployees": {
        "@type": "QuantitativeValue",
        "value": {{ $company->current_headcount }}
    },
    @endif
    @if($company->linkedin_url)
    "sameAs": [@json($company->linkedin_url)],
    @endif
    "additionalProperty": [
        @if($company->category)
        {
            "@type": "PropertyValue",
            "name": "category",
            "value": @json($company->category)
        },
        @endif
        @if($company->fundingRounds->sum('amount'))
        {
            "@type": "PropertyValue",
            "name": "totalFunding",
            "value": {{ $company->fundingRounds->sum('amount') }},
            "unitCode": "USD"
        },
        @endif
        {
            "@type": "PropertyValue",
            "name": "fundingRoundsCount",
            "value": {{ $company->fundingRounds->count() }}
        }
    ]
}
</script>
@endpush

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

    @if($company->headcountSnapshots->count() || $company->fundingRounds->count())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @if($company->headcountSnapshots->count())
                <div class="bg-white rounded-lg shadow-sm border p-6" id="headcount-section">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Employee Growth</h2>
                    @if($company->headcountSnapshots->count() >= 2)
                        <div class="h-64 chart-container" data-chart="headcount">
                            <div class="chart-loading flex items-center justify-center h-full text-gray-400">
                                <svg class="animate-spin h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <canvas id="headcountChart" class="hidden" role="img" aria-label="Headcount growth chart for {{ $company->name }}"></canvas>
                        </div>
                    @else
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Current</span>
                            <span class="font-semibold text-gray-900">{{ number_format($company->headcountSnapshots->first()?->headcount ?? 0) }} employees</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-3">More data points needed to show growth chart.</p>
                    @endif
                </div>
            @endif

            @if($company->fundingRounds->count())
                <div class="bg-white rounded-lg shadow-sm border p-6" id="funding-section">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Funding History</h2>
                    @if($company->fundingRounds->where('amount', '>', 0)->count() >= 2)
                        <div class="h-48 mb-4 chart-container" data-chart="funding">
                            <div class="chart-loading flex items-center justify-center h-full text-gray-400">
                                <svg class="animate-spin h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <canvas id="fundingChart" class="hidden" role="img" aria-label="Funding history chart for {{ $company->name }}"></canvas>
                        </div>
                    @endif
                    <div class="space-y-3 max-h-72 overflow-y-auto">
                        @foreach($company->fundingRounds->sortByDesc('announced_date') as $round)
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
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
        </div>
    @endif

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

@if($company->headcountSnapshots->count() >= 2 || $company->fundingRounds->where('amount', '>', 0)->count() >= 2)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    // Chart data (embedded to avoid blocking)
    const chartData = {
        headcount: @json($company->headcountSnapshots->sortBy('recorded_date')->values(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
        funding: @json($company->fundingRounds->where('amount', '>', 0)->sortBy('announced_date')->values(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
    };

    // Chart instances to track
    const chartInstances = {};

    // Format currency for display
    function formatCurrency(value) {
        if (value >= 1000000000) {
            return '$' + (value / 1000000000).toFixed(1) + 'B';
        } else if (value >= 1000000) {
            return '$' + (value / 1000000).toFixed(0) + 'M';
        } else if (value >= 1000) {
            return '$' + (value / 1000).toFixed(0) + 'K';
        }
        return '$' + value.toLocaleString();
    }

    // Sanitize text for display (defense in depth)
    function sanitizeText(text) {
        if (typeof text !== 'string') return '';
        return text.replace(/[<>&"']/g, function(c) {
            return {'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;',"'":'&#39;'}[c];
        });
    }

    // Round type colors
    const roundColors = {
        'seed': { bg: 'rgba(34, 197, 94, 0.8)', border: 'rgb(34, 197, 94)' },
        'series_a': { bg: 'rgba(59, 130, 246, 0.8)', border: 'rgb(59, 130, 246)' },
        'series_b': { bg: 'rgba(99, 102, 241, 0.8)', border: 'rgb(99, 102, 241)' },
        'series_c': { bg: 'rgba(139, 92, 246, 0.8)', border: 'rgb(139, 92, 246)' },
        'series_d': { bg: 'rgba(168, 85, 247, 0.8)', border: 'rgb(168, 85, 247)' },
        'series_e': { bg: 'rgba(192, 132, 252, 0.8)', border: 'rgb(192, 132, 252)' },
        'default': { bg: 'rgba(107, 114, 128, 0.8)', border: 'rgb(107, 114, 128)' }
    };

    function getRoundColor(roundType) {
        const type = (roundType || '').toLowerCase().replace(' ', '_');
        return roundColors[type] || roundColors['default'];
    }

    // Initialize headcount chart
    function initHeadcountChart() {
        const canvas = document.getElementById('headcountChart');
        if (!canvas || chartInstances.headcount) return;

        const container = canvas.closest('.chart-container');
        const loading = container.querySelector('.chart-loading');

        const snapshots = chartData.headcount;
        if (snapshots.length < 2) return;

        const labels = snapshots.map(s => {
            const date = new Date(s.recorded_date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', timeZone: 'UTC' });
        });
        const data = snapshots.map(s => s.headcount);

        chartInstances.headcount = new Chart(canvas.getContext('2d'), {
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
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                const current = context.parsed.y;
                                const lines = [current.toLocaleString() + ' employees'];

                                // Calculate % change from previous data point
                                const idx = context.dataIndex;
                                if (idx > 0) {
                                    const previous = data[idx - 1];
                                    if (previous > 0) {
                                        const pctChange = ((current - previous) / previous) * 100;
                                        const sign = pctChange >= 0 ? '+' : '';
                                        lines.push(sign + pctChange.toFixed(1) + '% from previous');
                                    }
                                }
                                return lines;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Show chart, hide loading
        if (loading) loading.classList.add('hidden');
        canvas.classList.remove('hidden');
    }

    // Initialize funding chart
    function initFundingChart() {
        const canvas = document.getElementById('fundingChart');
        if (!canvas || chartInstances.funding) return;

        const container = canvas.closest('.chart-container');
        const loading = container.querySelector('.chart-loading');

        const rounds = chartData.funding;
        if (rounds.length < 2) return;

        const labels = rounds.map(r => {
            const type = r.round_type ? sanitizeText(r.round_type).replace('_', ' ') : 'Round';
            const typeName = type.charAt(0).toUpperCase() + type.slice(1);
            // Add date below round type
            if (r.announced_date) {
                const date = new Date(r.announced_date);
                const dateStr = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric', timeZone: 'UTC' });
                return [typeName, dateStr];
            }
            return typeName;
        });
        const data = rounds.map(r => r.amount);
        const backgroundColors = rounds.map(r => getRoundColor(r.round_type).bg);
        const borderColors = rounds.map(r => getRoundColor(r.round_type).border);

        chartInstances.funding = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Amount Raised',
                    data: data,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        callbacks: {
                            title: function(context) {
                                const round = rounds[context[0].dataIndex];
                                const date = round.announced_date ? new Date(round.announced_date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : '';
                                return context[0].label + (date ? ' - ' + date : '');
                            },
                            label: function(context) {
                                return formatCurrency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });

        // Show chart, hide loading
        if (loading) loading.classList.add('hidden');
        canvas.classList.remove('hidden');
    }

    // Lazy load charts using Intersection Observer
    function setupLazyLoading() {
        const chartContainers = document.querySelectorAll('.chart-container');

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const chartType = entry.target.dataset.chart;
                        if (chartType === 'headcount') {
                            initHeadcountChart();
                        } else if (chartType === 'funding') {
                            initFundingChart();
                        }
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '100px',
                threshold: 0.1
            });

            chartContainers.forEach(container => {
                observer.observe(container);
            });
        } else {
            // Fallback for older browsers
            initHeadcountChart();
            initFundingChart();
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupLazyLoading);
    } else {
        setupLazyLoading();
    }
})();
</script>
@endpush
@endif
@endsection
