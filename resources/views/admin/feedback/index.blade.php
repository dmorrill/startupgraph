@extends('layouts.app')

@section('title', 'Feedback — Admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">User Feedback</h1>

    @if($feedback->count())
        <div class="space-y-4">
            @foreach($feedback as $item)
            <div class="bg-white border rounded-lg p-4">
                <div class="flex justify-between items-start mb-2">
                    <div class="text-sm text-gray-500">
                        {{ $item->user ? $item->user->name . ' (' . $item->user->email . ')' : 'Anonymous' }}
                    </div>
                    <div class="text-xs text-gray-400">{{ $item->created_at->diffForHumans() }}</div>
                </div>
                <p class="text-gray-900">{{ $item->message }}</p>
                @if($item->page_url)
                <div class="text-xs text-gray-400 mt-2">From: {{ $item->page_url }}</div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $feedback->links() }}
        </div>
    @else
        <div class="bg-white border rounded-lg p-8 text-center text-gray-500">
            No feedback yet.
        </div>
    @endif
</div>
@endsection
