@extends('layouts.app')

@section('title', $person->name . ' - StartupGraph')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('companies.index') }}" class="text-blue-600 hover:underline">Companies</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-600">{{ $person->name }}</span>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex flex-col sm:flex-row gap-6">
            @if($person->photo_url)
                <img src="{{ $person->photo_url }}" alt="{{ $person->name }}" class="w-32 h-32 rounded-full object-cover">
            @else
                <div class="w-32 h-32 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-3xl font-semibold">
                    {{ collect(explode(' ', $person->name))->map(fn($n) => substr($n, 0, 1))->join('') }}
                </div>
            @endif

            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">{{ $person->name }}</h1>

                @if($person->companies->count())
                    @php $currentRole = $person->companies->where('pivot.is_current', true)->first(); @endphp
                    @if($currentRole)
                        <p class="text-lg text-gray-600 mt-1">
                            {{ $currentRole->pivot->role }} at
                            <a href="{{ route('companies.show', $currentRole) }}" class="text-blue-600 hover:underline">{{ $currentRole->name }}</a>
                        </p>
                    @endif
                @endif

                <div class="flex flex-wrap gap-2 mt-4">
                    @if($person->linkedin_url)
                        <a
                            href="{{ $person->linkedin_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            LinkedIn
                        </a>
                    @endif
                    @if($person->twitter_url)
                        <a
                            href="{{ $person->twitter_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                        >
                            Twitter/X
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if($person->bio)
            <div class="mt-6 pt-6 border-t">
                <p class="text-gray-600 leading-relaxed">{{ $person->bio }}</p>
            </div>
        @endif
    </div>

    @if($person->companies->count())
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Experience</h2>
            <div class="space-y-4">
                @foreach($person->companies as $company)
                    <a href="{{ route('companies.show', $company) }}" class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">{{ $company->name }}</p>
                            @if($company->pivot->role)
                                <p class="text-gray-600">{{ $company->pivot->role }}</p>
                            @endif
                        </div>
                        <div class="text-right text-sm text-gray-500">
                            @if($company->pivot->is_current)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Current
                                </span>
                            @else
                                @if($company->pivot->started_at || $company->pivot->ended_at)
                                    {{ $company->pivot->started_at?->format('Y') ?? '?' }} - {{ $company->pivot->ended_at?->format('Y') ?? 'Present' }}
                                @endif
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
