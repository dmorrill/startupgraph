@php
    $isEdit = isset($company);
@endphp

<div class="space-y-6">
    {{-- Name --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">
            Name <span class="text-red-500">*</span>
        </label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $company->name ?? '') }}"
            required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Slug (only shown on edit) --}}
    @if($isEdit)
    <div>
        <label for="slug" class="block text-sm font-medium text-gray-700">
            Slug <span class="text-red-500">*</span>
        </label>
        <input
            type="text"
            name="slug"
            id="slug"
            value="{{ old('slug', $company->slug ?? '') }}"
            required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono @error('slug') border-red-500 @enderror"
        >
        <p class="mt-1 text-sm text-gray-500">URL: /companies/{{ old('slug', $company->slug ?? 'example-slug') }}</p>
        @error('slug')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @endif

    {{-- Website --}}
    <div>
        <label for="website" class="block text-sm font-medium text-gray-700">
            Website
        </label>
        <input
            type="url"
            name="website"
            id="website"
            value="{{ old('website', $company->website ?? '') }}"
            placeholder="https://example.com"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('website') border-red-500 @enderror"
        >
        @error('website')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Description --}}
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">
            Description
        </label>
        <textarea
            name="description"
            id="description"
            rows="4"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
        >{{ old('description', $company->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Category --}}
    <div>
        <label for="category" class="block text-sm font-medium text-gray-700">
            Category
        </label>
        <select
            name="category"
            id="category"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-500 @enderror"
        >
            <option value="">Select a category</option>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}" {{ old('category', $company->category ?? '') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('category')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Founded Date --}}
    <div>
        <label for="founded_date" class="block text-sm font-medium text-gray-700">
            Founded Date
        </label>
        <input
            type="date"
            name="founded_date"
            id="founded_date"
            value="{{ old('founded_date', isset($company) && $company->founded_date ? $company->founded_date->format('Y-m-d') : '') }}"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('founded_date') border-red-500 @enderror"
        >
        @error('founded_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Location --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="city" class="block text-sm font-medium text-gray-700">
                City
            </label>
            <input
                type="text"
                name="city"
                id="city"
                value="{{ old('city', $company->city ?? '') }}"
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-500 @enderror"
            >
            @error('city')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="state" class="block text-sm font-medium text-gray-700">
                State
            </label>
            <input
                type="text"
                name="state"
                id="state"
                value="{{ old('state', $company->state ?? '') }}"
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('state') border-red-500 @enderror"
            >
            @error('state')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="country" class="block text-sm font-medium text-gray-700">
                Country
            </label>
            <input
                type="text"
                name="country"
                id="country"
                value="{{ old('country', $company->country ?? '') }}"
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('country') border-red-500 @enderror"
            >
            @error('country')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- LinkedIn URL --}}
    <div>
        <label for="linkedin_url" class="block text-sm font-medium text-gray-700">
            LinkedIn URL
        </label>
        <input
            type="url"
            name="linkedin_url"
            id="linkedin_url"
            value="{{ old('linkedin_url', $company->linkedin_url ?? '') }}"
            placeholder="https://linkedin.com/company/example"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('linkedin_url') border-red-500 @enderror"
        >
        @error('linkedin_url')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
