<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Super Admin Dashboard'); ?> - Event Connect</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
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
    
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

        <!-- Sidebar -->
        <div id="sidebar" class="w-64 h-full shadow-lg mobile-sidebar" style="background-color: #B22234;">
            <!-- Logo Section -->
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-crown text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Event Connect</h1>
                        <p class="text-white/80 text-xs">Super Admin</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="mt-6">
                <!-- Management Section -->
                <div class="px-4 mb-4">
                    <p class="text-white/60 text-xs font-bold uppercase tracking-widest mb-3">Management</p>
                </div>

                <a href="<?php echo e(route('super.admin.dashboard')); ?>" 
                   class="flex items-center px-6 py-3 <?php echo e(request()->routeIs('super.admin.dashboard') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10'); ?>">
                    <i class="fas fa-chart-line w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>

                <a href="<?php echo e(route('super.admin.organizers')); ?>" 
                   class="flex items-center px-6 py-3 <?php echo e(request()->routeIs('super.admin.organizers*') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10'); ?>">
                    <i class="fas fa-crown w-5 mr-3"></i>
                    <span>Event Organizers</span>
                </a>

                <a href="<?php echo e(route('super.admin.events')); ?>" 
                   class="flex items-center px-6 py-3 <?php echo e(request()->routeIs('super.admin.events*') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10'); ?>">
                    <i class="fas fa-calendar-alt w-5 mr-3"></i>
                    <span>All Events</span>
                </a>

                <a href="<?php echo e(route('super.admin.categories')); ?>" 
                   class="flex items-center px-6 py-3 <?php echo e(request()->routeIs('super.admin.categories*') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10'); ?>">
                    <i class="fas fa-tags w-5 mr-3"></i>
                    <span>Categories</span>
                </a>

                <a href="<?php echo e(route('super.admin.users')); ?>" 
                   class="flex items-center px-6 py-3 <?php echo e(request()->routeIs('super.admin.users*') ? 'text-white bg-white/20 border-r-4 border-white' : 'text-white/80 hover:bg-white/10'); ?>">
                    <i class="fas fa-users w-5 mr-3"></i>
                    <span>Users Management</span>
                </a>

                <!-- System Section -->
                <div class="px-4 mt-8 mb-4 border-t border-white/20 pt-6">
                    <p class="text-white/60 text-xs font-bold uppercase tracking-widest mb-3">System</p>
                </div>

                <a href="<?php echo e(route('admin.dashboard')); ?>" 
                   class="flex items-center px-6 py-3 text-white/80 hover:bg-white/10">
                    <i class="fas fa-arrow-left w-5 mr-3"></i>
                    <span>Back to Admin</span>
                </a>
            </nav>

            <!-- User Profile Section at Bottom -->
            <div class="absolute bottom-0 w-64 p-6 border-t border-white/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">
                            <?php echo e(strtoupper(substr(Session::get('user')['name'] ?? 'SA', 0, 1))); ?>

                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white"><?php echo e(Session::get('user')['name'] ?? 'Super Admin'); ?></p>
                            <p class="text-xs text-white/70"><?php echo e(Session::get('user')['role'] ?? 'super_admin'); ?></p>
                        </div>
                    </div>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-white/80 hover:text-white transition-colors" title="Logout">
                            <i class="fas fa-sign-out-alt text-lg"></i>
                        </button>
                    </form>
                </div>
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
                            <h2 class="text-xl md:text-3xl font-bold text-gray-800"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h2>
                            <p class="text-xs md:text-sm text-gray-600 hidden sm:block"><?php echo $__env->yieldContent('page-description', 'Welcome to Super Admin Dashboard'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <span class="text-xs md:text-sm text-gray-600 hidden sm:inline">Welcome, <?php echo e(session('user')['name'] ?? 'Super Admin'); ?></span>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background-color: #B22234;">
                            <i class="fas fa-crown text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 md:p-6">
                <?php if(session('success')): ?>
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <h3 class="font-semibold mb-2">Errors</h3>
                        <ul class="list-disc list-inside">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>

    <!-- Mobile Menu Script -->
    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }

        mobileMenuBtn?.addEventListener('click', toggleSidebar);
        overlay?.addEventListener('click', toggleSidebar);

        // Close sidebar when a link is clicked
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    toggleSidebar();
                }
            });
        });
    </script>

    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\frontend\resources\views/admin/super-layout.blade.php ENDPATH**/ ?>