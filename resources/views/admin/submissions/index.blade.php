@extends('layouts.app')

@section('title', 'Review Submissions - Admin - StartupGraph')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Submissions</h1>
    <div class="flex space-x-2">
        <a href="{{ route('admin.submissions.index', ['status' => 'pending']) }}"
           class="px-3 py-1 text-sm rounded-md {{ request('status', 'pending') === 'pending' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700' }}">
            Pending
        </a>
        <a href="{{ route('admin.submissions.index', ['status' => 'approved']) }}"
           class="px-3 py-1 text-sm rounded-md {{ request('status') === 'approved' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700' }}">
            Approved
        </a>
        <a href="{{ route('admin.submissions.index', ['status' => 'rejected']) }}"
           class="px-3 py-1 text-sm rounded-md {{ request('status') === 'rejected' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700' }}">
            Rejected
        </a>
    </div>
</div>

@if (session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md mb-6">
        {{ session('success') }}
    </div>
@endif

@if ($submissions->isEmpty())
    <p class="text-gray-500">No {{ request('status', 'pending') }} submissions.</p>
@else
    <div class="bg-white shadow overflow-hidden rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Builder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($submissions as $submission)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $submission->name }}</div>
                            @if ($submission->description)
                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ $submission->description }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $submission->builder_name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if ($submission->url)
                                <a href="{{ $submission->url }}" target="_blank" class="text-blue-600 hover:underline">{{ parse_url($submission->url, PHP_URL_HOST) }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $submission->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                            @if ($submission->status === 'pending')
                                <form method="POST" action="{{ route('admin.submissions.approve', $submission) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 font-medium">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.submissions.reject', $submission) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Reject</button>
                                </form>
                            @else
                                <span class="text-gray-400">{{ ucfirst($submission->status) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $submissions->links() }}
    </div>
@endif
@endsection
