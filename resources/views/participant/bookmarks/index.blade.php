@extends('participant.layout')

@section('title', 'My Bookmarks - Event Connect')

@section('content')
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
                <a href="?filter=all" class="filter-tab {{ (!request('filter') || request('filter') == 'all') ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Semua
                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs {{ (!request('filter') || request('filter') == 'all') ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600' }}">
                        {{ $counts['all'] }}
                    </span>
                </a>
                <a href="?filter=upcoming" class="filter-tab {{ request('filter') == 'upcoming' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Upcoming
                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs {{ request('filter') == 'upcoming' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600' }}">
                        {{ $counts['upcoming'] }}
                    </span>
                </a>
                <a href="?category=saved" class="filter-tab {{ request('category') == 'saved' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Saved
                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs {{ request('category') == 'saved' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600' }}">
                        {{ $counts['saved'] }}
                    </span>
                </a>
                <a href="?category=interested" class="filter-tab {{ request('category') == 'interested' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Interested
                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs {{ request('category') == 'interested' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600' }}">
                        {{ $counts['interested'] }}
                    </span>
                </a>
                <a href="?filter=past" class="filter-tab {{ request('filter') == 'past' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Past
                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs {{ request('filter') == 'past' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600' }}">
                        {{ $counts['past'] }}
                    </span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Bookmarked Events Grid -->
    @if($bookmarks->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($bookmarks as $bookmark)
                @php
                    $event = $bookmark->event;
                @endphp
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <!-- Event Image -->
                    <div class="relative h-48 bg-gradient-to-r from-red-500 to-red-600">
                        @if($event->image)
                            <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full">
                                <i class="fas fa-calendar-alt text-white text-6xl opacity-50"></i>
                            </div>
                        @endif
                        
                        <!-- Bookmark Badge -->
                        <div class="absolute top-3 right-3">
                            <button onclick="removeBookmark({{ $event->id }}, this)" class="bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition shadow-lg" title="Remove bookmark">
                                <i class="fas fa-bookmark"></i>
                            </button>
                        </div>

                        <!-- Category Badge -->
                        @if($event->category)
                        <div class="absolute top-3 left-3">
                            <span class="px-3 py-1 bg-white text-gray-900 text-xs font-semibold rounded-full">
                                {{ $event->category->name }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Event Details -->
                    <div class="p-5">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
                            <a href="{{ route('events.show', $event->id) }}" class="hover:text-red-600">
                                {{ $event->title }}
                            </a>
                        </h3>

                        <!-- Organizer -->
                        <div class="flex items-center text-sm text-gray-600 mb-3">
                            <i class="far fa-user mr-2"></i>
                            <span>{{ $event->organizer->full_name ?? $event->organizer->name }}</span>
                        </div>

                        <!-- Date & Time -->
                        <div class="flex items-center text-sm text-gray-600 mb-2">
                            <i class="far fa-calendar mr-2"></i>
                            <span>{{ $event->start_date->format('d M Y') }}</span>
                        </div>

                        <!-- Location -->
                        <div class="flex items-center text-sm text-gray-600 mb-3">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span class="line-clamp-1">{{ $event->location }}</span>
                        </div>

                        <!-- Bookmark Info -->
                        <div class="border-t pt-3 mb-3">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span class="flex items-center">
                                    <i class="fas fa-tag mr-1"></i>
                                    {{ ucfirst($bookmark->category) }}
                                </span>
                                <span>{{ $bookmark->created_at->diffForHumans() }}</span>
                            </div>
                            @if($bookmark->notes)
                            <p class="mt-2 text-sm text-gray-600 italic line-clamp-2">
                                "{{ $bookmark->notes }}"
                            </p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a href="{{ route('events.show', $event->id) }}" class="flex-1 bg-red-600 text-white text-center py-2 rounded-lg hover:bg-red-700 transition font-medium">
                                View Details
                            </a>
                            @if($event->is_paid)
                            <div class="px-4 py-2 bg-green-100 text-green-800 rounded-lg font-semibold">
                                Rp {{ number_format($event->price, 0, ',', '.') }}
                            </div>
                            @else
                            <div class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg font-semibold">
                                Free
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($bookmarks->hasPages())
        <div class="mt-8">
            {{ $bookmarks->links() }}
        </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-bookmark text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Bookmark</h3>
            <p class="text-gray-600 mb-6">Anda belum menyimpan event apapun. Mulai explore dan bookmark event favorit Anda!</p>
            <a href="{{ route('events.index') }}" class="inline-block bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-medium">
                <i class="fas fa-search mr-2"></i>
                Explore Events
            </a>
        </div>
    @endif
</div>

<!-- JavaScript -->
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function removeBookmark(eventId, button) {
        if (!confirm('Remove this bookmark?')) return;

        try {
            const response = await fetch(`/participant/bookmarks/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                // Remove the card with animation
                const card = button.closest('.grid > div');
                card.style.transition = 'opacity 0.3s';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    
                    // Check if no more bookmarks
                    const grid = document.querySelector('.grid');
                    if (grid && grid.children.length === 0) {
                        location.reload();
                    }
                }, 300);

                showToast('Bookmark removed successfully', 'success');
            } else {
                showToast(data.message || 'Failed to remove bookmark', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to remove bookmark', 'error');
        }
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white shadow-lg z-50 ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
@endsection