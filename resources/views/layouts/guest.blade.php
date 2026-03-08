<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>StartupGraph</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-gray-900">
                        StartupGraph
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('companies.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">Companies</a>
                    <a href="{{ route('open-source.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">Open Source</a>
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 text-sm">Log in</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">Sign up</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex flex-col sm:justify-center items-center pt-12 sm:pt-20">
        <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>

    @include('partials.feedback-widget')
</body>
</html>
