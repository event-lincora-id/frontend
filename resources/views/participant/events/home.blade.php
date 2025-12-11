@extends('participant.layout')

@section('title', 'Home - Event Connect')

@push('head-scripts')
<!-- Bookmark Functions - MUST load BEFORE HTML -->
<script>
// Toast notification function - MUST be defined first
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

// Bookmark Manager dengan localStorage saja - GLOBAL SCOPE
window.BookmarkManager = {
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
            bookmarks.splice(index, 1);
            this.saveBookmarks(bookmarks);
            return false;
        } else {
            bookmarks.push(eventId);
            this.saveBookmarks(bookmarks);
            return true;
        }
    },
    
    isBookmarked(eventId) {
        return this.getBookmarks().includes(eventId);
    }
};

// Global function untuk toggle bookmark
window.toggleEventBookmark = function(eventId, btn) {
    console.log('🔖 Bookmark clicked!', eventId);
    
    const icon = btn.querySelector('i');
    const isBookmarked = window.BookmarkManager.toggle(eventId);
    
    console.log('is bookmarked:', isBookmarked);
    
    if (isBookmarked) {
        icon.classList.remove('far', 'text-gray-400');
        icon.classList.add('fas', 'text-red-600');
        showToast('Event bookmarked! 🔖', 'success');
    } else {
        icon.classList.remove('fas', 'text-red-600');
        icon.classList.add('far', 'text-gray-400');
        showToast('Bookmark removed', 'info');
    }
};

// Global handler untuk inline onclick
window.handleBookmarkClick = function(eventId, btn, e) {
    console.log('🎯 ONCLICK HANDLER CALLED!', eventId);
    e.preventDefault();
    e.stopPropagation();
    window.toggleEventBookmark(eventId, btn);
    return false;
};

console.log('✅ Bookmark script loaded! handleBookmarkClick:', typeof window.handleBookmarkClick);
</script>
@endpush

@section('content')
<div class="bg-gray-50">
    <!-- Hero Section with Search -->
    <div class="relative h-[500px] w-full flex items-center justify-center bg-cover bg-center overflow-hidden" style="background-image: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');">
        <div class="absolute inset-0 bg-gradient-to-br from-red-900/80 via-red-800/70 to-pink-900/80"></div>
        <div class="relative z-10 text-center text-white px-4 max-w-4xl mx-auto">
            <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">Discover Amazing Events</h1>
            <p class="text-xl text-white/95 mb-8 drop-shadow">Find and join events that match your interests</p>
            
            <!-- Search Bar -->
            <div class="max-w-2xl mx-auto">
                <form action="{{ route('events.index') }}" method="GET" class="flex gap-2">
                    <div class="flex-1 relative">
                        <input type="text" name="search" placeholder="Search events..." 
                            class="w-full px-6 py-4 rounded-full text-gray-900 focus:outline-none focus:ring-4 focus:ring-white/30 shadow-xl">
                        <i class="fas fa-search absolute right-6 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit" class="bg-white text-red-600 px-8 py-4 rounded-full font-semibold hover:bg-gray-100 transition-all shadow-xl">
                        Search
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($events->count() > 0)
        
        <!-- Event Recommendation Section -->
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Featured Events</h2>
                    <p class="text-gray-600 mt-1">Handpicked events just for you</p>
                </div>
                <a href="{{ route('events.index') }}" class="text-red-600 hover:text-red-700 font-semibold flex items-center gap-2">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="relative">
                <div class="px-14">
                    <div id="recommendationScroll" class="flex overflow-x-auto gap-6 pb-4 scrollbar-hide snap-x snap-mandatory" style="scrollbar-width: none; -ms-overflow-style: none;">
                        @foreach($events->take(6) as $event)
                            <div class="flex-shrink-0 w-80 snap-start group">
                                <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                                    <div class="relative h-52 overflow-hidden">
                                        @if($event->image_url)
                                            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
                                                <i class="fas fa-calendar-alt text-white text-6xl opacity-30"></i>
                                            </div>
                                        @endif
                                        <div class="absolute top-3 right-3">
                                            @if($event->price > 0)
                                                <span class="bg-white px-3 py-1 rounded-full text-sm font-semibold text-red-600 shadow-md">
                                                    Rp {{ number_format($event->price) }}
                                                </span>
                                            @else
                                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-md">
                                                    FREE
                                                </span>
                                            @endif
                                        </div>
                                        @if($event->category_name)
                                        <div class="absolute top-3 left-3">
                                            <span class="bg-red-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-md">
                                                {{ $event->category_name }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="p-5">
                                        <div class="flex items-start justify-between mb-3">
                                            <h3 class="font-bold text-lg text-gray-900 flex-1 line-clamp-2 group-hover:text-red-600 transition-colors">
                                                <a href="{{ route('events.show', $event->id) }}">
                                                    {{ $event->title }}
                                                </a>
                                            </h3>
                                            @if(session('user'))
                                            <button onclick="handleBookmarkClick({{ $event->id }}, this, event)" class="ml-2 p-2 rounded-full hover:bg-gray-100 transition-colors bookmark-btn relative z-10" data-event-id="{{ $event->id }}" style="pointer-events: auto;">
                                                <i class="far fa-bookmark text-gray-400"></i>
                                            </button>
                                            @endif
                                        </div>
                                        <div class="space-y-2 text-sm text-gray-600">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-calendar text-red-500 w-4"></i>
                                                <span>{{ $event->start_date->format('d M Y') }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-clock text-red-500 w-4"></i>
                                                <span>{{ $event->start_date->format('H:i') }} WIB</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-map-marker-alt text-red-500 w-4"></i>
                                                <span class="line-clamp-1">{{ $event->location }}</span>
                                            </div>
                                            @if(isset($event->participants_count))
                                            <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                                                <i class="fas fa-users text-red-500 w-4"></i>
                                                <span class="font-semibold text-gray-900">{{ $event->participants_count }} participants</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
                @foreach($events as $event)
                    <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 group">
                        <div class="relative h-52 overflow-hidden">
                            @if($event->image_url)
                                <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-white text-6xl opacity-30"></i>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0 group-hover:from-black/70 transition-all"></div>
                            <div class="absolute bottom-3 left-3 right-3">
                                @if($event->category_name)
                                <span class="inline-block bg-red-600 text-white px-3 py-1 rounded-full text-xs font-semibold mb-2">
                                    {{ $event->category_name }}
                                </span>
                                @endif
                            </div>
                            <div class="absolute top-3 right-3">
                                @if($event->price > 0)
                                    <span class="bg-white px-3 py-1 rounded-full text-sm font-semibold text-red-600 shadow-md">
                                        Rp {{ number_format($event->price) }}
                                    </span>
                                @else
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-md">
                                        FREE
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="font-bold text-lg text-gray-900 flex-1 line-clamp-2 group-hover:text-red-600 transition-colors">
                                    <a href="{{ route('events.show', $event->id) }}">
                                        {{ $event->title }}
                                    </a>
                                </h3>
                                @if(session('user'))
                                <button onclick="handleBookmarkClick({{ $event->id }}, this, event)" class="ml-2 p-2 rounded-full hover:bg-gray-100 transition-colors bookmark-btn relative z-10" data-event-id="{{ $event->id }}" style="pointer-events: auto;">
                                    <i class="far fa-bookmark text-gray-400"></i>
                                </button>
                                @endif
                            </div>
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-red-500 w-4"></i>
                                    <span>{{ $event->start_date->format('d M Y') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock text-red-500 w-4"></i>
                                    <span>{{ $event->start_date->format('H:i') }} WIB</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-red-500 w-4"></i>
                                    <span class="line-clamp-1">{{ $event->location }}</span>
                                </div>
                                @if(isset($event->participants_count))
                                <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                                    <i class="fas fa-users text-red-500 w-4"></i>
                                    <span class="font-semibold text-gray-900">{{ $event->participants_count }} participants</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        @else
        
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
                    @if(session('user') && (session('user')['role'] === 'admin' || session('user')['role'] === 'organizer'))
                        <a href="{{ route('admin.events') }}" class="inline-flex items-center justify-center bg-red-600 text-white px-8 py-3 rounded-full hover:bg-red-700 transition-all shadow-lg hover:shadow-xl font-semibold">
                            <i class="fas fa-plus-circle mr-2"></i>Create Event
                        </a>
                    @endif
                    <a href="{{ route('events.index') }}" class="inline-flex items-center justify-center border-2 border-red-600 text-red-600 px-8 py-3 rounded-full hover:bg-red-50 transition-all font-semibold">
                        <i class="fas fa-search mr-2"></i>Browse All Events
                    </a>
                </div>
            </div>
        </div>
        
        @endif
    </div>
</div>
@endsection

@section('scripts')
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
@endsection