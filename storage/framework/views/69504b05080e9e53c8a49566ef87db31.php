<?php $__env->startSection('title', 'Home - Event Connect'); ?>

<?php $__env->startPush('head-scripts'); ?>
<!-- API-based bookmark system -->
<script>
// Toast notification function
function showToast(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };

    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Bookmark Manager - Hybrid (localStorage + API sync)
window.BookmarkManager = {
    STORAGE_KEY: 'event_bookmarks',

    getBookmarks() {
        const bookmarks = localStorage.getItem(this.STORAGE_KEY);
        return bookmarks ? JSON.parse(bookmarks) : [];
    },

    saveBookmarks(bookmarks) {
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(bookmarks));
    },

    async toggle(eventId) {
        // Update localStorage immediately for instant UI
        const bookmarks = this.getBookmarks();
        const index = bookmarks.indexOf(eventId);
        let isBookmarked;

        if (index > -1) {
            bookmarks.splice(index, 1);
            isBookmarked = false;
        } else {
            bookmarks.push(eventId);
            isBookmarked = true;
        }

        this.saveBookmarks(bookmarks);

        // Sync with backend API in background
        try {
            const response = await fetch(`/participant/bookmarks/${eventId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!data.success) {
                console.error('Backend sync failed:', data.message);
            } else {
                console.log('✓ Synced with backend:', data);
            }
        } catch (error) {
            console.error('Backend sync error:', error);
        }

        return isBookmarked;
    },

    isBookmarked(eventId) {
        return this.getBookmarks().includes(eventId);
    }
};

// Global handler for bookmark clicks
window.handleBookmarkClick = async function(eventId, btn, e) {
    console.log('🎯 ONCLICK HANDLER CALLED!', eventId);
    e.preventDefault();
    e.stopPropagation();

    const icon = btn.querySelector('i');

    // Disable button during API call
    btn.disabled = true;
    btn.style.opacity = '0.6';

    try {
        const isBookmarked = await window.BookmarkManager.toggle(eventId);
        console.log('is_bookmarked:', isBookmarked);

        if (isBookmarked) {
            icon.classList.remove('far', 'text-gray-400');
            icon.classList.add('fas', 'text-red-600');
            showToast('Event bookmarked! 🔖', 'success');
        } else {
            icon.classList.remove('fas', 'text-red-600');
            icon.classList.add('far', 'text-gray-400');
            showToast('Bookmark removed', 'info');
        }
    } catch (error) {
        showToast('Failed to update bookmark. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.style.opacity = '1';
    }

    return false;
};

// Initialize bookmarks on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Initializing homepage bookmarks...');

    const bookmarkButtons = document.querySelectorAll('[onclick*="handleBookmarkClick"]');
    console.log(`Found ${bookmarkButtons.length} bookmark buttons`);

    // Update UI based on localStorage
    bookmarkButtons.forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick');
        const match = onclickAttr.match(/handleBookmarkClick\((\d+)/);

        if (match) {
            const eventId = parseInt(match[1]);
            const isBookmarked = window.BookmarkManager.isBookmarked(eventId);

            console.log(`Event ${eventId} bookmark status:`, isBookmarked);

            const icon = btn.querySelector('i');
            if (icon && isBookmarked) {
                icon.classList.remove('far', 'text-gray-400');
                icon.classList.add('fas', 'text-red-600');
                console.log(`✓ Event ${eventId} marked as bookmarked`);
            }
        }
    });

    console.log('✅ Bookmarks initialized!');
});

console.log('✅ Bookmark script loaded!');
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-gray-50">
    <!-- Hero Section with Search -->
    <div class="relative h-[400px] sm:h-[500px] w-full flex items-center justify-center bg-cover bg-center overflow-hidden" style="background-image: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');">
        <div class="absolute inset-0 bg-gradient-to-br from-red-900/80 via-red-800/70 to-pink-900/80"></div>
        <div class="relative z-10 text-center text-white px-4 max-w-4xl mx-auto">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-3 sm:mb-4 drop-shadow-lg">Discover Amazing Events</h1>
            <p class="text-base sm:text-lg md:text-xl text-white/95 mb-6 sm:mb-8 drop-shadow">Find and join events that match your interests</p>

            <!-- Search Bar -->
            <div class="max-w-2xl mx-auto">
                <form action="<?php echo e(route('events.index')); ?>" method="GET" class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 relative">
                        <input type="text" name="search" placeholder="Search events..."
                            class="w-full px-4 sm:px-6 py-3 sm:py-4 rounded-full text-gray-900 focus:outline-none focus:ring-4 focus:ring-white/30 shadow-xl">
                        <i class="fas fa-search absolute right-4 sm:right-6 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit" class="bg-white text-red-600 px-6 sm:px-8 py-3 sm:py-4 rounded-full font-semibold hover:bg-gray-100 transition-all shadow-xl">
                        Search
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if($events->count() > 0): ?>
        
        <!-- Event Recommendation Section -->
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Featured Events</h2>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">Handpicked events just for you</p>
                </div>
                <a href="<?php echo e(route('events.index')); ?>" class="text-red-600 hover:text-red-700 font-semibold flex items-center gap-1 sm:gap-2 text-sm sm:text-base">
                    View All <i class="fas fa-arrow-right text-xs sm:text-sm"></i>
                </a>
            </div>

            <div class="relative">
                <div class="px-0 sm:px-14">
                    <div id="recommendationScroll" class="flex overflow-x-auto gap-4 sm:gap-6 pb-4 scrollbar-hide snap-x snap-mandatory" style="scrollbar-width: none; -ms-overflow-style: none;">
                        <?php $__currentLoopData = $events->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex-shrink-0 w-72 sm:w-80 snap-start group">
                                <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                                    <div class="relative h-52 overflow-hidden">
                                        <?php if($event->image_url): ?>
                                            <img src="<?php echo e($event->image_url); ?>" alt="<?php echo e($event->title); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
                                                <i class="fas fa-calendar-alt text-white text-6xl opacity-30"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="absolute top-3 right-3">
                                            <?php if($event->price > 0): ?>
                                                <span class="bg-white px-3 py-1 rounded-full text-sm font-semibold text-red-600 shadow-md">
                                                    Rp <?php echo e(number_format($event->price)); ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-md">
                                                    FREE
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if($event->category_name): ?>
                                        <div class="absolute top-3 left-3">
                                            <span class="bg-red-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-md">
                                                <?php echo e($event->category_name); ?>

                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-5">
                                        <div class="flex items-start justify-between mb-3">
                                            <h3 class="font-bold text-lg text-gray-900 flex-1 line-clamp-2 group-hover:text-red-600 transition-colors">
                                                <a href="<?php echo e(route('events.show', $event->id)); ?>">
                                                    <?php echo e($event->title); ?>

                                                </a>
                                            </h3>
                                            <?php if(session('user')): ?>
                                            <button onclick="handleBookmarkClick(<?php echo e($event->id); ?>, this, event)" class="ml-2 p-2 rounded-full hover:bg-gray-100 transition-colors bookmark-btn relative z-10" data-event-id="<?php echo e($event->id); ?>" style="pointer-events: auto;">
                                                <i class="far fa-bookmark text-gray-400"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="space-y-2 text-sm text-gray-600">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-calendar text-red-500 w-4"></i>
                                                <span><?php echo e($event->start_date->format('d M Y')); ?></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-clock text-red-500 w-4"></i>
                                                <span><?php echo e($event->start_date->format('H:i')); ?> WIB</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-map-marker-alt text-red-500 w-4"></i>
                                                <span class="line-clamp-1"><?php echo e($event->location); ?></span>
                                            </div>
                                            <?php if(isset($event->participants_count)): ?>
                                            <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                                                <i class="fas fa-users text-red-500 w-4"></i>
                                                <span class="font-semibold text-gray-900"><?php echo e($event->participants_count); ?> participants</span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <button id="scrollLeftBtn" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-xl hover:bg-red-50 hover:text-red-600 z-30 transition-all border border-gray-200" onclick="scrollRecommendation('left')">
                    <i class="fas fa-chevron-left text-lg"></i>
                </button>
                <button id="scrollRightBtn" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-xl hover:bg-red-50 hover:text-red-600 z-30 transition-all border border-gray-200" onclick="scrollRecommendation('right')">
                    <i class="fas fa-chevron-right text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Upcoming Events Section -->
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Upcoming Events</h2>
                    <p class="text-gray-600 mt-1">Don't miss these exciting events</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 group">
                        <div class="relative h-52 overflow-hidden">
                            <?php if($event->image_url): ?>
                                <img src="<?php echo e($event->image_url); ?>" alt="<?php echo e($event->title); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <div class="w-full h-full bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-white text-6xl opacity-30"></i>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0 group-hover:from-black/70 transition-all"></div>
                            <div class="absolute bottom-3 left-3 right-3">
                                <?php if($event->category_name): ?>
                                <span class="inline-block bg-red-600 text-white px-3 py-1 rounded-full text-xs font-semibold mb-2">
                                    <?php echo e($event->category_name); ?>

                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="absolute top-3 right-3">
                                <?php if($event->price > 0): ?>
                                    <span class="bg-white px-3 py-1 rounded-full text-sm font-semibold text-red-600 shadow-md">
                                        Rp <?php echo e(number_format($event->price)); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-md">
                                        FREE
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="font-bold text-lg text-gray-900 flex-1 line-clamp-2 group-hover:text-red-600 transition-colors">
                                    <a href="<?php echo e(route('events.show', $event->id)); ?>">
                                        <?php echo e($event->title); ?>

                                    </a>
                                </h3>
                                <?php if(session('user')): ?>
                                <button onclick="handleBookmarkClick(<?php echo e($event->id); ?>, this, event)" class="ml-2 p-2 rounded-full hover:bg-gray-100 transition-colors bookmark-btn relative z-10" data-event-id="<?php echo e($event->id); ?>" style="pointer-events: auto;">
                                    <i class="far fa-bookmark text-gray-400"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-red-500 w-4"></i>
                                    <span><?php echo e($event->start_date->format('d M Y')); ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock text-red-500 w-4"></i>
                                    <span><?php echo e($event->start_date->format('H:i')); ?> WIB</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-red-500 w-4"></i>
                                    <span class="line-clamp-1"><?php echo e($event->location); ?></span>
                                </div>
                                <?php if(isset($event->participants_count)): ?>
                                <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                                    <i class="fas fa-users text-red-500 w-4"></i>
                                    <span class="font-semibold text-gray-900"><?php echo e($event->participants_count); ?> participants</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        
        <?php else: ?>
        
        <!-- Empty State -->
        <div class="text-center py-20">
            <div class="bg-white rounded-2xl shadow-xl p-12 max-w-2xl mx-auto">
                <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-calendar-times text-5xl text-red-500"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">No Events Available Yet</h3>
                <p class="text-gray-600 mb-6">There are currently no published events. Check back soon for exciting upcoming events!</p>
                
                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 text-left">
                    <h4 class="font-semibold text-blue-900 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Are you an Event Organizer?
                    </h4>
                    <p class="text-sm text-blue-800 mb-4">Create and publish events to share with our community!</p>
                    <div class="space-y-2 text-sm text-blue-700">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle mt-0.5"></i>
                            <span>Login with organizer account</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle mt-0.5"></i>
                            <span>Go to Admin Dashboard</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle mt-0.5"></i>
                            <span>Create new event and publish it</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <?php if(session('user') && (session('user')['role'] === 'admin' || session('user')['role'] === 'organizer')): ?>
                        <a href="<?php echo e(route('admin.events')); ?>" class="inline-flex items-center justify-center bg-red-600 text-white px-8 py-3 rounded-full hover:bg-red-700 transition-all shadow-lg hover:shadow-xl font-semibold">
                            <i class="fas fa-plus-circle mr-2"></i>Create Event
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('events.index')); ?>" class="inline-flex items-center justify-center border-2 border-red-600 text-red-600 px-8 py-3 rounded-full hover:bg-red-50 transition-all font-semibold">
                        <i class="fas fa-search mr-2"></i>Browse All Events
                    </a>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .line-clamp-1 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
    }
    .line-clamp-2 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
</style>
<script>
function scrollRecommendation(direction) {
    const container = document.getElementById('recommendationScroll');
    if (container) {
        const scrollAmount = 360; // Width of card (320px) + gap (40px)
        container.scrollBy({ left: direction === 'left' ? -scrollAmount : scrollAmount, behavior: 'smooth' });
    }
}

// Auto-hide scroll buttons if not needed
window.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('recommendationScroll');
    const leftBtn = document.getElementById('scrollLeftBtn');
    const rightBtn = document.getElementById('scrollRightBtn');
    
    if (container && leftBtn && rightBtn) {
        function updateButtons() {
            const scrollLeft = container.scrollLeft;
            const maxScroll = container.scrollWidth - container.clientWidth;
            
            leftBtn.style.opacity = scrollLeft <= 0 ? '0.3' : '1';
            leftBtn.style.pointerEvents = scrollLeft <= 0 ? 'none' : 'auto';
            
            rightBtn.style.opacity = scrollLeft >= maxScroll ? '0.3' : '1';
            rightBtn.style.pointerEvents = scrollLeft >= maxScroll ? 'none' : 'auto';
        }
        
        container.addEventListener('scroll', updateButtons);
        updateButtons();
    }
    
    // Initialize bookmarks state
    initializeBookmarks();
});

// Initialize bookmarks function
function initializeBookmarks() {
    console.log('✅ Initializing bookmarks...');
    const buttons = document.querySelectorAll('.bookmark-btn');
    console.log('Found buttons:', buttons.length);
    
    buttons.forEach((btn, index) => {
        const eventId = parseInt(btn.dataset.eventId);
        const icon = btn.querySelector('i');
        
        console.log(`Button ${index}: Event ID ${eventId}`);
        
        // Set initial state from localStorage
        if (window.BookmarkManager.isBookmarked(eventId)) {
            icon.classList.remove('far', 'text-gray-400');
            icon.classList.add('fas', 'text-red-600');
        }
    });
    
    console.log('✅ Bookmarks initialized!');
}

</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('participant.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Study\Kuliah\Semester-7\CP\event-connect\resources\views/participant/events/home.blade.php ENDPATH**/ ?>