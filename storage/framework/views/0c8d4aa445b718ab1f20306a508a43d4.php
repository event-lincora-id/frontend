

<?php $__env->startSection('title', 'My Bookmarks - Event Connect'); ?>

<?php $__env->startPush('head-scripts'); ?>
<script>
// Load all events data for bookmarks
window.allEventsData = <?php echo json_encode($allEvents ?? [], 15, 512) ?>;
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Bookmarks</h1>
        <p class="mt-2 text-gray-600">Event yang telah Anda simpan</p>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px space-x-8 px-6">
                <a href="#" data-filter="all" class="filter-tab border-red-600 text-red-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Semua
                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs bg-red-100 text-red-600" id="count-all">0</span>
                </a>
                <a href="#" data-filter="upcoming" class="filter-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Upcoming
                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600" id="count-upcoming">0</span>
                </a>
                <a href="#" data-filter="past" class="filter-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Past
                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600" id="count-past">0</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Bookmarked Events Grid -->
    <div id="bookmarks-container">
        <!-- Will be populated by JavaScript -->
    </div>
    
    <!-- Empty State (initially hidden) -->
    <div id="empty-state" class="bg-white rounded-lg shadow-sm p-12 text-center hidden">
        <i class="fas fa-bookmark text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Bookmark</h3>
        <p class="text-gray-600 mb-6">Anda belum menyimpan event apapun. Mulai explore dan bookmark event favorit Anda!</p>
        <a href="<?php echo e(route('events.index')); ?>" class="inline-block bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-medium">
            <i class="fas fa-search mr-2"></i>
            Explore Events
        </a>
    </div>
</div>

<script>
// Bookmark Manager - localStorage ONLY
const BookmarkManager = {
    STORAGE_KEY: 'event_bookmarks',
    
    getBookmarks() {
        const bookmarks = localStorage.getItem(this.STORAGE_KEY);
        return bookmarks ? JSON.parse(bookmarks) : [];
    },
    
    remove(eventId) {
        const bookmarks = this.getBookmarks();
        const filtered = bookmarks.filter(id => id !== eventId);
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(filtered));
        return filtered;
    }
};

// Render bookmarked events
function renderBookmarks(filter = 'all') {
    const bookmarkedIds = BookmarkManager.getBookmarks();
    const allEvents = window.allEventsData || [];
    const container = document.getElementById('bookmarks-container');
    const emptyState = document.getElementById('empty-state');
    
    // Filter events by bookmark IDs
    let bookmarkedEvents = allEvents.filter(event => bookmarkedIds.includes(event.id));
    
    // Apply filter
    const now = new Date();
    if (filter === 'upcoming') {
        bookmarkedEvents = bookmarkedEvents.filter(event => new Date(event.start_date) > now);
    } else if (filter === 'past') {
        bookmarkedEvents = bookmarkedEvents.filter(event => new Date(event.end_date || event.start_date) < now);
    }
    
    // Update counts
    const upcomingCount = allEvents.filter(e => bookmarkedIds.includes(e.id) && new Date(e.start_date) > now).length;
    const pastCount = allEvents.filter(e => bookmarkedIds.includes(e.id) && new Date(e.end_date || e.start_date) < now).length;
    
    document.getElementById('count-all').textContent = bookmarkedIds.length;
    document.getElementById('count-upcoming').textContent = upcomingCount;
    document.getElementById('count-past').textContent = pastCount;
    
    // Show/hide empty state
    if (bookmarkedEvents.length === 0) {
        container.innerHTML = '';
        container.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    emptyState.classList.add('hidden');
    
    // Render events
    container.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            ${bookmarkedEvents.map(event => `
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="relative h-48 bg-gradient-to-r from-red-500 to-red-600">
                        ${event.image_url ? 
                            `<img src="${event.image_url}" alt="${event.title}" class="w-full h-full object-cover">` :
                            `<div class="flex items-center justify-center h-full">
                                <i class="fas fa-calendar-alt text-white text-6xl opacity-50"></i>
                            </div>`
                        }
                        <div class="absolute top-3 right-3">
                            <button onclick="removeBookmark(${event.id})" class="bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition shadow-lg" title="Remove bookmark">
                                <i class="fas fa-bookmark"></i>
                            </button>
                        </div>
                        ${event.category_name ? 
                            `<div class="absolute top-3 left-3">
                                <span class="px-3 py-1 bg-white text-gray-900 text-xs font-semibold rounded-full">
                                    ${event.category_name}
                                </span>
                            </div>` : ''
                        }
                    </div>
                    <div class="p-5">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
                            <a href="/events/${event.id}" class="hover:text-red-600">
                                ${event.title}
                            </a>
                        </h3>
                        <div class="flex items-center text-sm text-gray-600 mb-2">
                            <i class="far fa-calendar mr-2"></i>
                            <span>${new Date(event.start_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'})}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600 mb-3">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span class="line-clamp-1">${event.location}</span>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <a href="/events/${event.id}" class="flex-1 bg-red-600 text-white text-center py-2 rounded-lg hover:bg-red-700 transition font-medium">
                                View Details
                            </a>
                            ${event.price > 0 ?
                                `<div class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg font-semibold text-sm">
                                    Rp ${Number(event.price).toLocaleString('id-ID')}
                                </div>` :
                                `<div class="px-4 py-2 bg-green-100 text-green-800 rounded-lg font-semibold text-sm">
                                    FREE
                                </div>`
                            }
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function removeBookmark(eventId) {
    if (!confirm('Remove this bookmark?')) return;
    
    BookmarkManager.remove(eventId);
    renderBookmarks(currentFilter);
    showToast('Bookmark removed', 'info');
}

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

// Filter handling
let currentFilter = 'all';

document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', (e) => {
        e.preventDefault();
        currentFilter = tab.dataset.filter;
        
        // Update active tab
        document.querySelectorAll('.filter-tab').forEach(t => {
            t.classList.remove('border-red-600', 'text-red-600');
            t.classList.add('border-transparent', 'text-gray-500');
            t.querySelector('span').classList.remove('bg-red-100', 'text-red-600');
            t.querySelector('span').classList.add('bg-gray-100', 'text-gray-600');
        });
        
        tab.classList.remove('border-transparent', 'text-gray-500');
        tab.classList.add('border-red-600', 'text-red-600');
        tab.querySelector('span').classList.remove('bg-gray-100', 'text-gray-600');
        tab.querySelector('span').classList.add('bg-red-100', 'text-red-600');
        
        renderBookmarks(currentFilter);
    });
});

// Initial render
document.addEventListener('DOMContentLoaded', () => {
    console.log('Loading bookmarks...', window.allEventsData);
    renderBookmarks('all');
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('participant.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\event-connect\resources\views/participant/bookmarks/index.blade.php ENDPATH**/ ?>