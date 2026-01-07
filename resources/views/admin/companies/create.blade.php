@extends('admin.layouts.app')

@section('title', 'Add Company - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.companies.index') }}" class="text-blue-600 hover:underline">Companies</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-600">Add Company</span>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Company</h1>

        <form method="POST" action="{{ route('admin.companies.store') }}">
            @csrf

            @include('admin.companies._form', ['categories' => $categories])

            <div class="mt-8 flex items-center gap-4">
                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Create Company
                </button>
                <a
                    href="{{ route('admin.companies.index') }}"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
