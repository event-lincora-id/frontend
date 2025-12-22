<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Event Connect</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Base sidebar positioning - starts hidden on mobile, fixed on all screens initially */
        .mobile-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            z-index: 40;
        }

        /* Active state - slide in from left */
        .mobile-sidebar.active {
            transform: translateX(0) !important;
        }

        /* Desktop - show as part of layout, don't slide */
        @media (min-width: 1024px) {
            .mobile-sidebar {
                position: relative !important;
                transform: translateX(0) !important;
                left: auto !important;
                top: auto !important;
                bottom: auto !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100" style="background-color: var(--color-background);">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

        <!-- Sidebar -->
        <div id="sidebar" class="w-64 h-full shadow-lg mobile-sidebar" style="background-color: #B22234;">
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
                <a href="{{ route('admin.finance.index') }}"
                   class="flex items-center px-6 py-3 {{ request()->routeIs('admin.finance*') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10' }}">
                    <i class="fas fa-coins mr-3"></i>
                    Finance & Withdrawals
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
        <div class="flex-1 overflow-y-auto w-full min-w-0 bg-gray-100">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b sticky top-0 z-10">
                <div class="px-4 md:px-6 py-4 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <!-- Mobile Menu Button -->
                        <button id="mobile-menu-btn" class="lg:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                            <i class="fas fa-bars text-2xl"></i>
                        </button>
                        <div>
                            <h2 class="text-xl md:text-3xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                            <p class="text-xs md:text-sm text-gray-600 hidden sm:block">@yield('page-description', 'Welcome back! Here\'s what\'s happening with your events.')</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <span class="text-xs md:text-sm text-gray-600 hidden sm:inline">Welcome, {{ session('user')['full_name'] ?? session('user')['name'] ?? 'User' }}</span>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background-color: var(--color-primary);">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 md:p-6">
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

    <!-- Mobile Menu Script -->
    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            console.log('Toggling sidebar'); // Debug log
            sidebar.classList.toggle('active');
            overlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');

            // Force reflow to ensure transition works
            void sidebar.offsetWidth;
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Mobile menu button click
        mobileMenuBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });

        // Overlay click
        overlay?.addEventListener('click', closeSidebar);

        // Close sidebar when clicking a link on mobile
        const sidebarLinks = sidebar?.querySelectorAll('a');
        sidebarLinks?.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    closeSidebar();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });

        // Ensure sidebar is closed on page load for mobile
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth < 1024) {
                closeSidebar();
            }
        });
    </script>

    @yield('scripts')
</body>
</html>

