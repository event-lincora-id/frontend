<?php $__env->startSection('title', 'Browse Events - Event Connect'); ?>

<?php $__env->startPush('head-scripts'); ?>
<!-- Removed old localStorage-based bookmark code - now using API-based bookmarks -->
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white">
    <!-- Hero Section (Dark Red) -->
    <div class="bg-red-800 h-32 w-full flex items-center justify-center">
        <div class="text-center text-white">
            <h1 class="text-4xl font-bold">Browse Events</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Search and Filter Section -->
        <div class="mb-8" id="filterSection">
            <form method="GET" action="<?php echo e(route('events.index')); ?>" id="searchForm">
                <!-- Search Bar - 2 Input Horizontal with Category Filter -->
                <div class="flex gap-4 items-end">
                    <!-- Search Event Name -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Event</label>
                        <input type="text" 
                               name="search" 
                               value="<?php echo e($currentSearch ?? ''); ?>"
                               placeholder="Search by event name..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="w-64">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" 
                                        <?php echo e(($currentCategory ?? '') == $category->id ? 'selected' : ''); ?>>
                                    <?php echo e($category->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <!-- Search Button -->
                    <div>
                        <button type="submit" 
                                class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 flex items-center gap-2 transition-colors">
                            <i class="fas fa-search"></i>
                            <span>Search</span>
                        </button>
                    </div>
                    
                    <!-- Clear Filters Button -->
                    <?php if($currentSearch || $currentCategory): ?>
                    <div>
                        <a href="<?php echo e(route('events.index')); ?>" 
                           class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 flex items-center gap-2 transition-colors">
                            <i class="fas fa-times"></i>
                            <span>Clear</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Active Filters Info -->
                <?php if($currentSearch || $currentCategory): ?>
                <div class="mt-4 flex items-center gap-2 text-sm text-gray-600">
                    <span class="font-medium">Active filters:</span>
                    <?php if($currentSearch): ?>
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full">
                            Search: "<?php echo e($currentSearch); ?>"
                        </span>
                    <?php endif; ?>
                    <?php if($currentCategory): ?>
                        <?php
                            $selectedCategory = $categories->firstWhere('id', $currentCategory);
                        ?>
                        <?php if($selectedCategory): ?>
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                                Category: <?php echo e($selectedCategory->name); ?>

                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Categories Quick Access -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Browse by Category</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <a href="<?php echo e(route('events.index')); ?>" 
                   class="px-4 py-3 rounded-lg text-center transition-all <?php echo e(!$currentCategory ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'); ?>">
                    <div class="font-medium">All Events</div>
                </a>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('events.index', ['category_id' => $category->id])); ?>" 
                       class="px-4 py-3 rounded-lg text-center transition-all <?php echo e(($currentCategory ?? '') == $category->id ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'); ?>">
                        <div class="font-medium text-sm"><?php echo e($category->name); ?></div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

       <!-- Results Section -->
<div class="mb-8">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-900">
            <?php if($currentSearch || $currentCategory): ?>
                Search Results
            <?php else: ?>
                All Events
            <?php endif; ?>
            <span class="text-gray-500 text-lg ml-2">(<?php echo e($events->count()); ?> events)</span>
        </h2>
    </div>
    
    <?php if(isset($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
            <p><?php echo e($error); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if($events->count() > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition-all duration-300">
                    <!-- Event Image -->
                    <div class="relative h-48 bg-gray-300">
                        <?php if($event->image_url): ?>
                            <img src="<?php echo e($event->image_url); ?>" alt="<?php echo e($event->title); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-white text-6xl opacity-30"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Category Badge -->
                        <?php if($event->category): ?>
                            <div class="absolute top-3 left-3">
                                <span class="bg-red-600 text-white text-xs font-semibold px-3 py-1 rounded-full shadow-lg">
                                    <?php echo e($event->category->name); ?>

                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Price Badge -->
                        <div class="absolute bottom-3 right-3">
                            <?php if($event->price > 0): ?>
                                <span class="bg-yellow-400 text-gray-900 text-sm font-bold px-3 py-1 rounded-full shadow-lg">
                                    Rp <?php echo e(number_format($event->price, 0, ',', '.')); ?>

                                </span>
                            <?php else: ?>
                                <span class="bg-green-500 text-white text-sm font-bold px-3 py-1 rounded-full shadow-lg">
                                    FREE
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Bookmark Button -->
                        <?php if(session('user')): ?>
                            <button
                                onclick="event.preventDefault(); event.stopPropagation(); toggleBookmarkCard(<?php echo e($event->id); ?>, this);"
                                class="bookmark-btn-card absolute top-3 right-3 w-10 h-10 rounded-full shadow-lg transition-all duration-200 flex items-center justify-center bg-white text-gray-700 hover:bg-gray-100"
                                data-event-id="<?php echo e($event->id); ?>"
                                data-bookmarked="false"
                                title="Bookmark this event"
                            >
                                <i class="fas fa-bookmark"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Event Details -->
                    <div class="p-5">
                        <h3 class="font-bold text-lg text-gray-900 mb-3 line-clamp-2 hover:text-red-600 transition-colors">
                            <a href="<?php echo e(route('events.show', $event->id)); ?>">
                                <?php echo e($event->title); ?>

                            </a>
                        </h3>
                        
                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt w-5 text-red-500"></i>
                                <span><?php echo e($event->start_date->format('D, M d, Y')); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock w-5 text-red-500"></i>
                                <span><?php echo e($event->start_date->format('H:i')); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt w-5 text-red-500"></i>
                                <span class="line-clamp-1"><?php echo e($event->location); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-users w-5 text-red-500"></i>
                                <span><?php echo e($event->registered_count ?? 0); ?> / <?php echo e($event->quota ?? 0); ?> participants</span>
                            </div>
                        </div>
                        
                        <a href="<?php echo e(route('events.show', $event->id)); ?>" 
                           class="block w-full text-center bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors font-medium">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <!-- No Results -->
        <div class="text-center py-16">
            <div class="max-w-md mx-auto">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <?php if(request('search') || request('category')): ?>
                        <i class="fas fa-search text-5xl text-gray-400"></i>
                    <?php else: ?>
                        <i class="fas fa-calendar-times text-5xl text-gray-400"></i>
                    <?php endif; ?>
                </div>
                
                <?php if(request('search') || request('category')): ?>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No Events Match Your Search</h3>
                    <p class="text-gray-600 mb-6">We couldn't find any events matching "<?php echo e(request('search')); ?>"
                        <?php if(request('category')): ?> in <?php echo e($categories->firstWhere('id', request('category'))->name ?? 'this category'); ?> <?php endif; ?>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="<?php echo e(route('events.index')); ?>" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors font-medium">
                            <i class="fas fa-times mr-2"></i>Clear Filters
                        </a>
                        <button onclick="history.back()" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                            <i class="fas fa-arrow-left mr-2"></i>Go Back
                        </button>
                    </div>
                <?php else: ?>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No Events Available Yet</h3>
                    <p class="text-gray-600 mb-6">There are currently no published events. Check back soon!</p>
                    
                    <!-- Info Box for Organizers -->
                    <?php if(session('user') && (session('user')['role'] === 'admin' || session('user')['role'] === 'organizer')): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 text-left">
                            <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                                <i class="fas fa-lightbulb mr-2"></i>
                                You can create events!
                            </h4>
                            <p class="text-sm text-blue-800 mb-4">As an organizer, you can create and publish events for the community.</p>
                            <a href="<?php echo e(route('admin.events')); ?>" class="inline-flex items-center text-sm font-medium text-blue-700 hover:text-blue-900">
                                Go to Admin Dashboard <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?php echo e(route('home')); ?>" class="inline-block bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition-colors font-medium shadow-md">
                        <i class="fas fa-home mr-2"></i>Back to Home
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
<script>
    // Scroll recommendation section
    function scrollRecommendation(direction) {
        const container = document.getElementById('recommendationScroll');
        if (container) {
            const scrollAmount = 336; // card width (320px) + gap (16px)
            if (direction === 'left') {
                container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        }
    }

    // Handle sort order change (if filter section is shown)
    const sortSelect = document.querySelector('select[name="sort_by"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const order = selectedOption.getAttribute('data-order');
            document.getElementById('sort_order').value = order;
        });
    }

    // Clear all filters
    function clearFilters() {
        const form = document.getElementById('searchForm');
        if (form) {
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type === 'checkbox') {
                    input.checked = false;
                } else if (input.type === 'text' || input.tagName === 'SELECT') {
                    input.value = '';
                }
            });
            // Remove hidden price inputs so stale values don't persist
            ['price_min', 'price_max'].forEach(name => {
                const hidden = form.querySelector(`input[name="${name}"]`);
                if (hidden) hidden.remove();
            });
            form.submit();
        }
    }

    // Auto-submit form on filter change (if filter section is shown)
    if (document.querySelector('select[name="category_id"]')) {
        document.querySelectorAll('select[name="category_id"], select[name="price_range"], select[name="date_filter"], select[name="sort_by"]').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('searchForm').submit();
            });
        });
    }

    // Handle price range filter (if filter section is shown)
    const priceRangeSelect = document.querySelector('select[name="price_range"]');
    if (priceRangeSelect) {
        priceRangeSelect.addEventListener('change', function() {
            const form = document.getElementById('searchForm');
            const value = this.value;

            // Always remove previous hidden price inputs first
            ['price_min', 'price_max'].forEach(name => {
                const hidden = form.querySelector(`input[name="${name}"]`);
                if (hidden) hidden.remove();
            });

            if (value === '') {
                // Any Price - nothing to add
            } else if (value === 'free') {
                addHiddenInput('price_min', '0');
                addHiddenInput('price_max', '0');
            } else if (value === '1000000+') {
                addHiddenInput('price_min', '1000000');
                // No max for 1M+
            } else if (value.includes('-')) {
                const [min, max] = value.split('-');
                addHiddenInput('price_min', min);
                addHiddenInput('price_max', max);
            }

            form.submit();
        });
    }

    function addHiddenInput(name, value) {
        // Remove existing hidden input
        const existing = document.querySelector(`input[name="${name}"]`);
        if (existing) {
            existing.remove();
        }
        
        // Add new hidden input
        const form = document.getElementById('searchForm');
        if (form) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        }
    }
</script>

<script>
    // Bookmark Manager - localStorage-based
    const BookmarkManager = {
        STORAGE_KEY: 'event_bookmarks',

        getBookmarks() {
            const bookmarks = localStorage.getItem(this.STORAGE_KEY);
            return bookmarks ? JSON.parse(bookmarks) : [];
        },

        saveBookmarks(bookmarks) {
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(bookmarks));
        },

        toggle(eventId) {
            const bookmarks = this.getBookmarks();
            const index = bookmarks.indexOf(eventId);

            if (index > -1) {
                // Remove bookmark
                bookmarks.splice(index, 1);
                this.saveBookmarks(bookmarks);
                return false; // not bookmarked
            } else {
                // Add bookmark
                bookmarks.push(eventId);
                this.saveBookmarks(bookmarks);
                return true; // bookmarked
            }
        },

        check(eventId) {
            return this.getBookmarks().includes(eventId);
        }
    };

    // Make function globally accessible for onclick handlers
    window.toggleBookmarkCard = function(eventId, button) {
        console.log('🔖 Bookmark card clicked!', eventId);

        try {
            const isBookmarked = BookmarkManager.toggle(eventId);

            // Update UI based on toggle result
            if (isBookmarked) {
                button.classList.remove('bg-white', 'text-gray-700', 'hover:bg-gray-100');
                button.classList.add('bg-red-600', 'text-white');
                button.dataset.bookmarked = 'true';
                button.title = 'Remove bookmark';
                showToast('Bookmarked! 🎉', 'success');
            } else {
                button.classList.remove('bg-red-600', 'text-white');
                button.classList.add('bg-white', 'text-gray-700', 'hover:bg-gray-100');
                button.dataset.bookmarked = 'false';
                button.title = 'Bookmark this event';
                showToast('Bookmark removed', 'info');
            }
        } catch (error) {
            console.error('Bookmark error:', error);
            showToast('Failed to update bookmark', 'error');
        }
    };

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white shadow-lg z-50 ${
            type === 'success' ? 'bg-green-600' : 
            type === 'error' ? 'bg-red-600' : 
            'bg-blue-600'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Initialize bookmarks on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ Initializing explore bookmarks...');

        const buttons = document.querySelectorAll('.bookmark-btn-card');
        console.log(`Found ${buttons.length} bookmark buttons`);

        // Check bookmark status from localStorage for each button
        buttons.forEach(btn => {
            const eventId = parseInt(btn.dataset.eventId);

            if (eventId) {
                const isBookmarked = BookmarkManager.check(eventId);
                console.log(`Event ${eventId} bookmark status:`, isBookmarked);

                if (isBookmarked) {
                    btn.classList.remove('bg-white', 'text-gray-700');
                    btn.classList.add('bg-red-600', 'text-white');
                    btn.setAttribute('data-bookmarked', 'true');
                    console.log(`✓ Event ${eventId} marked as bookmarked`);
                }
            }
        });

        console.log('✅ Bookmarks initialized!');
    });
</script>

<?php $__env->stopPush(); ?>





<?php echo $__env->make('participant.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\frontend\resources\views/participant/events/index.blade.php ENDPATH**/ ?>