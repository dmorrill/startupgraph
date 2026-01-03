@extends('admin.layouts.app')

@section('title', 'Edit ' . $company->name . ' - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.companies.index') }}" class="text-blue-600 hover:underline">Companies</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-600">{{ $company->name }}</span>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Company</h1>
            <a
                href="{{ route('companies.show', $company) }}"
                target="_blank"
                class="text-sm text-blue-600 hover:text-blue-800"
            >
                View public page
            </a>
        </div>

        <form method="POST" action="{{ route('admin.companies.update', $company) }}">
            @csrf
            @method('PUT')

            @include('admin.companies._form', ['company' => $company, 'categories' => $categories])

            <div class="mt-8 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Update Company
                    </button>
                    <a
                        href="{{ route('admin.companies.index') }}"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                    >
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Delete Section --}}
    <div class="bg-white rounded-lg shadow-sm border p-6 border-red-200">
        <h2 class="text-lg font-bold text-red-600 mb-4">Danger Zone</h2>
        <p class="text-gray-600 mb-4">
            Deleting this company will also remove all related data including funding rounds, headcount snapshots, news mentions, and team member associations. This action cannot be undone.
        </p>
        <form
            method="POST"
            action="{{ route('admin.companies.destroy', $company) }}"
            onsubmit="return confirm('Are you sure you want to delete {{ addslashes($company->name) }}? This action cannot be undone.');"
        >
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
                Delete Company
            </button>
        </form>
    </div>
</div>
@endsection
