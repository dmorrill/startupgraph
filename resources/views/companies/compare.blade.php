@extends('layouts.app')

@section('title', 'Compare Companies - StartupGraph')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Compare Companies</h1>
    </div>

    {{-- Company Selector --}}
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form method="GET" action="{{ route('companies.compare') }}" id="compare-form" class="space-y-4">
            <p class="text-sm text-gray-600">Select 2–4 companies to compare side by side.</p>
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label for="company-search" class="block text-sm font-medium text-gray-700 mb-1">Add a company</label>
                    <select id="company-search" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 border">
                        <option value="">Search companies...</option>
                        @foreach($allCompanies as $c)
                            <option value="{{ $c->slug }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Compare
                </button>
            </div>

            <input type="hidden" name="companies" id="companies-input" value="{{ implode(',', $slugs) }}">

            @if(count($slugs))
                <div class="flex flex-wrap gap-2" id="selected-tags">
                    @foreach($companies as $c)
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm" data-slug="{{ $c->slug }}">
                            {{ $c->name }}
                            <button type="button" onclick="removeCompany('{{ $c->slug }}')" class="hover:text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </span>
                    @endforeach
                </div>
            @endif
        </form>
    </div>

    @if($companies->count() < 2)
        <div class="bg-white rounded-lg shadow-sm border p-12 text-center">
            <p class="text-gray-500 text-lg">Select at least 2 companies above to start comparing.</p>
            <p class="text-gray-400 text-sm mt-2">You can compare up to 4 companies at once.</p>
        </div>
    @else
        {{-- Side-by-Side Overview --}}
        <div class="bg-white rounded-lg shadow-sm border overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b">
                        <th class="p-4 text-sm font-medium text-gray-500 w-40">Metric</th>
                        @foreach($companies as $company)
                            <th class="p-4 text-sm font-semibold text-gray-900 min-w-[180px]">
                                <a href="{{ route('companies.show', $company) }}" class="hover:text-blue-600">
                                    {{ $company->name }}
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr>
                        <td class="p-4 text-sm text-gray-500">Category</td>
                        @foreach($companies as $company)
                            <td class="p-4 text-sm text-gray-900">{{ $company->category_label ?? '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 text-sm text-gray-500">Location</td>
                        @foreach($companies as $company)
                            <td class="p-4 text-sm text-gray-900">
                                {{ collect([$company->city, $company->state, $company->country])->filter()->join(', ') ?: '—' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 text-sm text-gray-500">Founded</td>
                        @foreach($companies as $company)
                            <td class="p-4 text-sm text-gray-900">{{ $company->founded_date ? $company->founded_date->format('M Y') : '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 text-sm text-gray-500">Employees</td>
                        @foreach($companies as $company)
                            <td class="p-4 text-sm font-semibold text-gray-900">
                                {{ $company->current_headcount ? number_format($company->current_headcount) : '—' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 text-sm text-gray-500">Total Funding</td>
                        @foreach($companies as $company)
                            @php $total = $company->funding_rounds_sum_amount; @endphp
                            <td class="p-4 text-sm font-semibold text-gray-900">
                                @if($total)
                                    @if($total >= 1000000000)
                                        ${{ number_format($total / 1000000000, 1) }}B
                                    @else
                                        ${{ number_format($total / 1000000, 0) }}M
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 text-sm text-gray-500">Funding Rounds</td>
                        @foreach($companies as $company)
                            <td class="p-4 text-sm text-gray-900">{{ $company->funding_rounds_count ?: '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 text-sm text-gray-500">Last Round</td>
                        @foreach($companies as $company)
                            @php $lastRound = $company->fundingRounds->sortByDesc('announced_date')->first(); @endphp
                            <td class="p-4 text-sm text-gray-900">
                                @if($lastRound)
                                    {{ ucfirst(str_replace('_', ' ', $lastRound->round_type ?? 'Unknown')) }}
                                    @if($lastRound->announced_date)
                                        <span class="text-gray-500">({{ $lastRound->announced_date->format('M Y') }})</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="p-4 text-sm text-gray-500">Key Investors</td>
                        @foreach($companies as $company)
                            @php
                                $investors = $company->fundingRounds->flatMap(function ($r) { return $r->investors; })->unique('id')->take(3);
                            @endphp
                            <td class="p-4 text-sm text-gray-900">
                                {{ $investors->pluck('name')->join(', ') ?: '—' }}
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Headcount Chart --}}
        @php
            $hasHeadcount = $companies->contains(function ($c) { return $c->headcountSnapshots->count() >= 2; });
        @endphp
        @if($hasHeadcount)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Employee Growth</h2>
                <div class="h-80">
                    <canvas id="headcountCompareChart"></canvas>
                </div>
            </div>
        @endif

        {{-- Funding Chart --}}
        @php
            $hasFunding = $companies->contains(function ($c) { return $c->fundingRounds->where('amount', '>', 0)->count() >= 1; });
        @endphp
        @if($hasFunding)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Funding Comparison</h2>
                <div class="h-80">
                    <canvas id="fundingCompareChart"></canvas>
                </div>
            </div>
        @endif
    @endif
</div>

<script>
var selectedSlugs = @json($slugs);

document.getElementById('company-search').addEventListener('change', function() {
    var slug = this.value;
    if (!slug || selectedSlugs.indexOf(slug) !== -1) { this.value = ''; return; }
    if (selectedSlugs.length >= 4) { alert('Maximum 4 companies'); this.value = ''; return; }
    selectedSlugs.push(slug);
    document.getElementById('companies-input').value = selectedSlugs.join(',');
    document.getElementById('compare-form').submit();
});

function removeCompany(slug) {
    var idx = selectedSlugs.indexOf(slug);
    if (idx > -1) selectedSlugs.splice(idx, 1);
    document.getElementById('companies-input').value = selectedSlugs.join(',');
    document.getElementById('compare-form').submit();
}
</script>

@if($companies->count() >= 2)
@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var colors = [
        { border: 'rgb(59, 130, 246)', bg: 'rgba(59, 130, 246, 0.1)' },
        { border: 'rgb(239, 68, 68)', bg: 'rgba(239, 68, 68, 0.1)' },
        { border: 'rgb(34, 197, 94)', bg: 'rgba(34, 197, 94, 0.1)' },
        { border: 'rgb(168, 85, 247)', bg: 'rgba(168, 85, 247, 0.1)' },
    ];

    var companiesData = @json($chartData);

    function formatCurrency(value) {
        if (value >= 1e9) return '$' + (value / 1e9).toFixed(1) + 'B';
        if (value >= 1e6) return '$' + (value / 1e6).toFixed(0) + 'M';
        if (value >= 1e3) return '$' + (value / 1e3).toFixed(0) + 'K';
        return '$' + value;
    }

    var hcCanvas = document.getElementById('headcountCompareChart');
    if (hcCanvas) {
        var datasets = [];
        for (var i = 0; i < companiesData.length; i++) {
            var c = companiesData[i];
            if (c.headcount.length >= 2) {
                var data = [];
                for (var j = 0; j < c.headcount.length; j++) {
                    data.push({ x: c.headcount[j].date, y: c.headcount[j].headcount });
                }
                datasets.push({
                    label: c.name,
                    data: data,
                    borderColor: colors[i].border,
                    backgroundColor: colors[i].bg,
                    fill: false,
                    tension: 0.3,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                });
            }
        }
        if (datasets.length) {
            new Chart(hcCanvas.getContext('2d'), {
                type: 'line',
                data: { datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    scales: {
                        x: { type: 'time', time: { unit: 'month' }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { callback: function(v) { return v.toLocaleString(); } } }
                    },
                    plugins: { legend: { position: 'top' } }
                }
            });
        }
    }

    var fCanvas = document.getElementById('fundingCompareChart');
    if (fCanvas) {
        var labels = [];
        var totals = [];
        var bgColors = [];
        var borderColors = [];
        for (var i = 0; i < companiesData.length; i++) {
            labels.push(companiesData[i].name);
            var sum = 0;
            for (var j = 0; j < companiesData[i].funding.length; j++) {
                sum += companiesData[i].funding[j].amount;
            }
            totals.push(sum);
            bgColors.push(colors[i].border.replace('rgb', 'rgba').replace(')', ', 0.8)'));
            borderColors.push(colors[i].border);
        }
        new Chart(fCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Funding',
                    data: totals,
                    backgroundColor: bgColors,
                    borderColor: borderColors,
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { callback: function(v) { return formatCurrency(v); } } }
                }
            }
        });
    }
});
</script>
@endpush
@endif

@endsection
