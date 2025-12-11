@extends('participant.layout')

@section('title', 'Dashboard - Event Connect')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Participant Dashboard</h1>
            <p class="mt-2 text-gray-600">Manage your event participations and activities</p>
        </div>

        @if(isset($error))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}
        </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-red-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Events Joined</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $stats['total_registered'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-certificate text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Events Attended</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $stats['attended_events'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-star text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Events</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $stats['upcoming_events'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Upcoming Events (User Joined) -->
            <div class="lg:col-span-2">
                <!-- Your Upcoming Events -->
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Your Upcoming Events</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @forelse ($upcomingEvents as $participant)
                                @php
                                    $event = $participant->event ?? null;
                                    if (!$event) continue;
                                    $startDate = isset($event->start_date) ? \Carbon\Carbon::parse($event->start_date) : null;
                                @endphp
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $event->title ?? 'No Title' }}</h4>
                                            <div class="space-y-1 text-sm text-gray-600">
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-calendar text-red-500 w-4"></i>
                                                    <span>{{ $startDate ? $startDate->format('d M Y') : 'Date TBA' }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-clock text-red-500 w-4"></i>
                                                    <span>{{ $startDate ? $startDate->format('H:i') : '--:--' }} WIB</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-map-marker-alt text-red-500 w-4"></i>
                                                    <span>{{ $event->location ?? 'Online Event' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-2 ml-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i>Registered
                                            </span>
                                            <a href="{{ route('events.show', $event->id ?? 0) }}" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                                View Details <i class="fas fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-calendar-alt text-3xl text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-500 mb-4">You haven't joined any upcoming events yet.</p>
                                    <a href="{{ route('events.index') }}" class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                        <i class="fas fa-search mr-2"></i>Browse Events
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Bookmarked Events -->
                <div class="bg-white shadow-lg rounded-xl overflow-hidden mt-6">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Bookmarked Events</h3>
                    </div>
                    <div class="p-6">
                        <div id="bookmarkedEventsContainer" class="space-y-4">
                            <!-- Loading state -->
                            <div class="text-center py-12">
                                <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-4"></i>
                                <p class="text-gray-500">Loading bookmarks...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('events.index') }}" class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-sm font-semibold rounded-lg text-white bg-red-600 hover:bg-red-700 transition-colors shadow-md">
                                <i class="fas fa-search mr-2"></i>Browse Events
                            </a>
                            <a href="{{ route('my.participations') }}" class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors shadow-md">
                                <i class="fas fa-qrcode mr-2"></i>My QR Codes
                            </a>
                            <a href="{{ route('participant.profile') }}" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-user mr-2"></i>Edit Profile
                            </a>
                            <a href="#" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-certificate mr-2"></i>My Certificates
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help Guide -->
                <div class="bg-gradient-to-br from-red-50 to-pink-50 shadow-lg rounded-xl overflow-hidden border border-red-100">
                    <div class="px-6 py-5 border-b border-red-100">
                        <h3 class="text-lg font-semibold text-gray-900">Getting Started</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 font-bold text-sm">1</span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-700"><strong>Browse Events</strong></p>
                                    <p class="text-xs text-gray-600 mt-1">Explore available events and find what interests you</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 font-bold text-sm">2</span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-700"><strong>Register</strong></p>
                                    <p class="text-xs text-gray-600 mt-1">Click "Join Event" to register for an event</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 font-bold text-sm">3</span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-700"><strong>Get QR Code</strong></p>
                                    <p class="text-xs text-gray-600 mt-1">Receive your QR code for event attendance</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 font-bold text-sm">4</span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-700"><strong>Attend Event</strong></p>
                                    <p class="text-xs text-gray-600 mt-1">Show your QR code at the event for check-in</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
// Bookmark Management with localStorage
const BookmarkManager = {
    STORAGE_KEY: 'event_bookmarks',
    
    getBookmarks() {
        const bookmarks = localStorage.getItem(this.STORAGE_KEY);
        return bookmarks ? JSON.parse(bookmarks) : [];
    },
    
    saveBookmarks(bookmarks) {
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(bookmarks));
    },
    
    addBookmark(eventId) {
        const bookmarks = this.getBookmarks();
        if (!bookmarks.includes(eventId)) {
            bookmarks.push(eventId);
            this.saveBookmarks(bookmarks);
            return true;
        }
        return false;
    },
    
    removeBookmark(eventId) {
        const bookmarks = this.getBookmarks();
        const filtered = bookmarks.filter(id => id !== eventId);
        this.saveBookmarks(filtered);
        return true;
    },
    
    isBookmarked(eventId) {
        return this.getBookmarks().includes(eventId);
    }
};

function removeBookmark(eventId) {
    if (confirm('Remove this event from bookmarks?')) {
        BookmarkManager.removeBookmark(eventId);
        loadBookmarkedEvents();
        showToast('Bookmark removed', 'info');
    }
}

// Load bookmarked events from API
async function loadBookmarkedEvents() {
    const container = document.getElementById('bookmarkedEventsContainer');
    const bookmarkIds = BookmarkManager.getBookmarks();
    
    if (bookmarkIds.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bookmark text-3xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 mb-4">No bookmarked events yet.</p>
                <a href="{{ route('events.index') }}" class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Discover Events
                </a>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-gray-400"></i></div>';
    
    try {
        // Fetch event details for each bookmarked event
        const eventPromises = bookmarkIds.slice(0, 3).map(async (eventId) => {
            try {
                const response = await fetch(`{{ config('services.backend.base_url') }}/events/${eventId}`);
                if (!response.ok) throw new Error('Event not found');
                const data = await response.json();
                return data.data;
            } catch (error) {
                console.error(`Failed to fetch event ${eventId}:`, error);
                // Remove invalid bookmark
                BookmarkManager.removeBookmark(eventId);
                return null;
            }
        });
        
        const events = (await Promise.all(eventPromises)).filter(e => e !== null);
        
        if (events.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <p class="text-gray-500">Bookmarked events not found.</p>
                    <button onclick="localStorage.removeItem('event_bookmarks'); loadBookmarkedEvents();" class="mt-2 text-red-600 hover:underline">
                        Clear Invalid Bookmarks
                    </button>
                </div>
            `;
            return;
        }
        
        container.innerHTML = events.map(event => `
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">${event.title}</h4>
                        <div class="space-y-1 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-calendar text-red-500 w-4"></i>
                                <span>${formatDate(event.start_date)}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-map-marker-alt text-red-500 w-4"></i>
                                <span>${event.location || 'Online Event'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2 ml-4">
                        <button onclick="removeBookmark(${event.id})" class="text-blue-600 hover:text-blue-700 transition-colors">
                            <i class="fas fa-bookmark text-lg"></i>
                        </button>
                        <a href="/events/${event.id}" class="text-red-600 hover:text-red-700 text-sm font-medium">
                            View <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
        
        if (bookmarkIds.length > 3) {
            container.innerHTML += `
                <div class="mt-4 text-center">
                    <a href="{{ route('events.index') }}" class="text-red-600 hover:text-red-700 font-medium">
                        View All Bookmarks (${bookmarkIds.length}) <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading bookmarks:', error);
        container.innerHTML = '<div class="text-center py-4 text-red-600">Failed to load bookmarks</div>';
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function showToast(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-in`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Load bookmarks on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBookmarkedEvents();
});
</script>
@endsection

@endsection