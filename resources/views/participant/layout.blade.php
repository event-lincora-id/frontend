<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Event Connect')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @stack('head-scripts')
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-red-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo/Website Name on Left -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center">
                        <i class="fas fa-calendar-alt text-white text-2xl mr-2"></i>
                        <span class="text-xl font-bold text-white">Event Connect</span>
                    </a>
                </div>
                
                <!-- Navigation Tabs -->
                <div class="flex items-center space-x-1 mx-auto">
                    <a href="{{ route('home') }}" 
                       class="px-4 py-2 rounded-md text-sm font-medium {{ request()->routeIs('home') ? 'bg-red-700 text-white' : 'bg-white text-gray-900 hover:bg-gray-100' }}">
                        Home
                    </a>
                    <a href="{{ route('events.index', ['explore' => true]) }}" 
                       class="px-4 py-2 rounded-md text-sm font-medium {{ request()->routeIs('events.index') ? 'bg-red-700 text-white' : 'bg-white text-gray-900 hover:bg-gray-100' }}">
                        Explore
                    </a>
                    <a href="{{ route('participant.dashboard') }}" 
                       class="px-4 py-2 rounded-md text-sm font-medium {{ request()->routeIs('participant.dashboard') ? 'bg-red-700 text-white' : 'bg-white text-gray-900 hover:bg-gray-100' }}">
                        Participation
                    </a>
                    @if(session('user'))
                    <a href="{{ route('participant.bookmarks.index') }}" 
                    class="px-4 py-2 rounded-md text-sm font-medium {{ request()->routeIs('participant.bookmarks.*') ? 'bg-red-700 text-white' : 'bg-white text-gray-900 hover:bg-gray-100' }}">
                        Bookmarks
                    </a>
                    @endif
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center space-x-2">
                    @if(session('user'))
                        <span class="text-white text-sm font-medium mr-2">
                            Welcome, {{ session('user')['full_name'] ?? session('user')['name'] ?? session('user')['email'] }}
                        </span>
                        <div class="flex items-center space-x-2">
                            <!-- Notifications dengan badge -->
                            <div class="relative">
                                <a href="{{ route('participant.notifications.index') }}" class="w-8 h-8 bg-white rounded-md text-gray-900 hover:bg-gray-100 flex items-center justify-center" title="Notifikasi">
                                    <i class="fas fa-bell text-sm"></i>
                                    <span id="notification-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white text-xs rounded-full flex items-center justify-center hidden">
                                        0
                                    </span>
                                </a>
                            </div>
                            <script>
                            // Load unread notification count
                            async function loadNotificationCount() {
                                try {
                                    const response = await fetch('/participant/notifications/unread-count');
                                    const data = await response.json();
                                    
                                    if (data.success && data.count > 0) {
                                        const badge = document.getElementById('notification-badge');
                                        if (badge) {
                                            badge.textContent = data.count > 99 ? '99+' : data.count;
                                            badge.classList.remove('hidden');
                                        }
                                    }
                                } catch (error) {
                                    console.error('Failed to load notification count:', error);
                                }
                            }

                            // Load on page load
                            document.addEventListener('DOMContentLoaded', loadNotificationCount);

                            // Refresh every 30 seconds
                            setInterval(loadNotificationCount, 30000);
                            </script>
                            <!-- Guide shortcut -->
                            <a href="{{ route('guide') }}" class="w-8 h-8 bg-white rounded-md text-gray-900 hover:bg-gray-100 flex items-center justify-center" title="Panduan">
                                <i class="fas fa-book-open text-sm"></i>
                            </a>
                            <!-- Profile dropdown -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="w-8 h-8 bg-white rounded-md text-gray-900 hover:bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-user text-sm"></i>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                    <a href="{{ route('participant.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user mr-2"></i>Profile
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}" class="block">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('guide') }}" class="w-8 h-8 bg-white rounded-md text-gray-900 hover:bg-gray-100 flex items-center justify-center" title="Panduan">
                                <i class="fas fa-book-open text-sm"></i>
                            </a>
                            <a href="{{ route('login') }}" class="px-4 py-2 rounded-md text-sm font-medium bg-white text-gray-900 hover:bg-gray-100">
                                Login
                            </a>
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-md text-sm font-medium bg-white text-gray-900 hover:bg-gray-100">
                                Register
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">Event Connect</h3>
                    <p class="text-gray-400">Platform terbaik untuk menemukan dan mengelola event favoritmu.</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="{{ route('events.index') }}" class="text-gray-400 hover:text-white">Browse Events</a></li>
                        <li><a href="{{ route('guide') }}" class="text-gray-400 hover:text-white">Panduan</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Contact</h3>
                    <ul class="space-y-2">
                        <li class="text-gray-400"><i class="fas fa-envelope mr-2"></i>info@eventconnect.com</li>
                        <li class="text-gray-400"><i class="fas fa-phone mr-2"></i>+62 123 4567 890</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Event Connect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Alpine.js for dropdown -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('scripts')
</body>
</html>