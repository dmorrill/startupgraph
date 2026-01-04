@extends('admin.layouts.app')

@section('title', 'Manage Companies - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manage Companies</h1>
            <p class="text-gray-600">{{ $companies->total() }} companies total</p>
        </div>
        <a
            href="{{ route('admin.companies.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Company
        </a>
    </div>

    <form method="GET" action="{{ route('admin.companies.index') }}" class="bg-white p-4 rounded-lg shadow-sm border">
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
            <button
                type="submit"
                class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors"
            >
                Search
            </button>
            @if(request('search'))
                <a
                    href="{{ route('admin.companies.index') }}"
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
                            Company
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Slug
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($companies as $company)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $company->name }}</div>
                                @if($company->website)
                                    <div class="text-sm text-gray-500">{{ parse_url($company->website, PHP_URL_HOST) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($company->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $company->category_label }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ collect([$company->city, $company->country])->filter()->join(', ') ?: '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                {{ $company->slug }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('companies.show', $company) }}" class="text-gray-600 hover:text-gray-900" target="_blank">
                                    View
                                </a>
                                <a href="{{ route('admin.companies.edit', $company) }}" class="text-blue-600 hover:text-blue-900">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No companies found.
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
