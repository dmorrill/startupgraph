<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - StartupGraph')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('head')
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-gray-900 text-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('admin.companies.index') }}" class="text-xl font-bold">
                        StartupGraph Admin
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.companies.index') }}" class="text-gray-300 hover:text-white">
                        Companies
                    </a>
                    <a href="{{ route('admin.oss-projects.index') }}" class="text-gray-300 hover:text-white">
                        OSS Projects
                    </a>
                    <span class="text-gray-600">|</span>
                    <a href="{{ route('home') }}" class="text-gray-300 hover:text-white">
                        View Site
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-gray-900 text-gray-400 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-sm">
                StartupGraph Admin Panel
            </p>
        </div>
    </footer>
</body>
</html>
