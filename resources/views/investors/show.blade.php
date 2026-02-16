@extends('layouts.app')

@section('title', $investor->name . ' - StartupGraph')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('investors.index') }}" class="text-blue-600 hover:underline">Investors</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-600">{{ $investor->name }}</span>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $investor->name }}</h1>
                @if($investor->type)
                    <span class="inline-flex items-center px-3 py-1 mt-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $investor->type_label }}
                    </span>
                @endif
            </div>
            @if($investor->website)
                <a href="{{ $investor->website }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Visit Website
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            @endif
        </div>

        @if($investor->description)
            <p class="text-gray-600 mt-4 leading-relaxed">{{ $investor->description }}</p>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mt-6 pt-6 border-t">
            <div>
                <p class="text-sm text-gray-500">Portfolio Companies</p>
                <p class="text-lg font-semibold text-gray-900">{{ $companies->count() }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Funding Rounds</p>
                <p class="text-lg font-semibold text-gray-900">{{ $investor->fundingRounds->count() }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Invested</p>
                <p class="text-lg font-semibold text-gray-900">
                    @if($totalInvested >= 1000000000)
                        ${{ number_format($totalInvested / 1000000000, 1) }}B
                    @elseif($totalInvested >= 1000000)
                        ${{ number_format($totalInvested / 1000000, 0) }}M
                    @else
                        ${{ number_format($totalInvested) }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    @if($companies->count())
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Portfolio Companies</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($companies as $company)
                    <a href="{{ route('companies.show', $company) }}" class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <p class="font-semibold text-gray-900">{{ $company->name }}</p>
                        @if($company->description)
                            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $company->description }}</p>
                        @endif
                        @if($company->city || $company->country)
                            <p class="text-xs text-gray-400 mt-2">{{ collect([$company->city, $company->country])->filter()->join(', ') }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($investor->fundingRounds->count())
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Investment History</h2>
            <div class="space-y-3">
                @foreach($investor->fundingRounds as $round)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <a href="{{ route('companies.show', $round->company) }}" class="font-semibold text-blue-600 hover:underline">
                                {{ $round->company->name }}
                            </a>
                            <p class="text-sm text-gray-500">
                                {{ ucfirst(str_replace('_', ' ', $round->round_type ?? 'Round')) }}
                                @if($round->pivot->is_lead) <span class="text-green-600 font-medium">• Lead</span> @endif
                            </p>
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
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
