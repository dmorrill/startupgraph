@extends('layouts.app')

@section('title', 'Submit a Project - StartupGraph')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Submit a Project</h1>
    <p class="text-gray-600 mb-8">Know an interesting indie startup or AI-built project? Submit it for inclusion in StartupGraph.</p>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('submissions.store') }}" class="space-y-6">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Company / Project Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 border">
        </div>

        <div>
            <label for="url" class="block text-sm font-medium text-gray-700">Website URL</label>
            <input type="url" name="url" id="url" value="{{ old('url') }}" placeholder="https://"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 border">
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 border">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="builder_name" class="block text-sm font-medium text-gray-700">Builder Name</label>
            <input type="text" name="builder_name" id="builder_name" value="{{ old('builder_name') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 border">
        </div>

        <div>
            <label for="tech_stack" class="block text-sm font-medium text-gray-700">Tech Stack</label>
            <input type="text" name="tech_stack" id="tech_stack" value="{{ old('tech_stack') }}" placeholder="Laravel, React, Cursor, etc."
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 border">
        </div>

        <div>
            <label for="submitter_email" class="block text-sm font-medium text-gray-700">Your Email (optional)</label>
            <input type="email" name="submitter_email" id="submitter_email" value="{{ old('submitter_email') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 border">
        </div>

        <div>
            <label for="source_url" class="block text-sm font-medium text-gray-700">Launch Post Link</label>
            <p class="text-xs text-gray-500 mt-0.5">Link to X/Twitter, LinkedIn, Product Hunt, or other launch post</p>
            <input type="url" name="source_url" id="source_url" value="{{ old('source_url') }}" placeholder="https://"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm px-3 py-2 border">
        </div>

        <div>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Submit Project
            </button>
        </div>
    </form>
</div>
@endsection
