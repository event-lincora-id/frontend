<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Event Connect</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100" style="background-color: var(--color-background);">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 shadow-lg" style="background-color: #B22234;">
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Event Connect</h1>
                        <p class="text-white/80 text-xs">Admin Dashboard</p>
                    </div>
                </div>
            </div>
            <nav class="mt-6">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-6 py-3 {{ request()->routeIs('admin.dashboard') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10' }}">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center px-6 py-3 {{ request()->routeIs('admin.users.*') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10' }}">
                    <i class="fas fa-users mr-3"></i>
                    Users
                </a>
                <a href="{{ route('admin.events.index') }}" 
                   class="flex items-center px-6 py-3 {{ request()->routeIs('admin.events.*') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10' }}">
                    <i class="fas fa-calendar-alt mr-3"></i>
                    Events
                </a>
                <a href="{{ route('admin.categories.index') }}" 
                   class="flex items-center px-6 py-3 {{ request()->routeIs('admin.categories.*') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10' }}">
                    <i class="fas fa-tags mr-3"></i>
                    Categories
                </a>
                <a href="{{ route('admin.analytics') }}" 
                   class="flex items-center px-6 py-3 {{ request()->routeIs('admin.analytics*') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10' }}">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Analytics
                </a>
            </nav>
            
            <!-- Logout Button -->
            <div class="absolute bottom-0 w-64 p-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-6 py-3 text-white/80 hover:bg-white/10 hover:text-white transition-colors">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                        <p class="text-gray-600">@yield('page-description', 'Welcome back! Here\'s what\'s happening with your events.')</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Welcome, {{ session('user')['full_name'] ?? session('user')['name'] ?? 'User' }}</span>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background-color: var(--color-primary);">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>
</html>

