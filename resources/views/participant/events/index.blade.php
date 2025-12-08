@extends('participant.layout')

@section('title', 'Browse Events - Event Connect')

@section('content')
<div class="bg-white">
    <!-- Hero Section (Dark Red) -->
    <div class="bg-red-800 h-32 w-full flex items-center justify-center">
        <div class="text-center text-white">
            <h1 class="text-4xl font-bold">Browse Events</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Search and Filter Section (Always show in Explore) -->
        <div class="mb-8" id="filterSection">
            <form method="GET" action="{{ route('events.index') }}" id="searchForm">
                <!-- Search Bar - 3 Input Horizontal -->
                <div class="flex gap-4 items-end">
                    <!-- Nama Event -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Event</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Nama Event"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                    
                    <!-- Tanggal -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <select name="date_filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <option value="">Any Date</option>
                            <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="tomorrow" {{ request('date_filter') == 'tomorrow' ? 'selected' : '' }}>Tomorrow</option>
                            <option value="this_week" {{ request('date_filter') == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="next_week" {{ request('date_filter') == 'next_week' ? 'selected' : '' }}>Next Week</option>
                            <option value="this_month" {{ request('date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        </select>
                    </div>
                    
                    <!-- Free/Price -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Free</label>
                        <select name="price_range" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            @php
                                $derivedRange = '';
                                $min = request('price_min');
                                $max = request('price_max');
                                if ($min !== null && $max !== null && $min == 0 && $max == 0) {
                                    $derivedRange = 'free';
                                } elseif ($min == 0 && $max == 100000) {
                                    $derivedRange = '0-100000';
                                } elseif ($min == 100000 && $max == 500000) {
                                    $derivedRange = '100000-500000';
                                } elseif ($min == 500000 && $max == 1000000) {
                                    $derivedRange = '500000-1000000';
                                } elseif ($min == 1000000 && ($max === null || $max === '')) {
                                    $derivedRange = '1000000+';
                                }
                                $currentPriceRange = request('price_range', $derivedRange);
                            @endphp
                            <option value="" {{ $currentPriceRange === '' ? 'selected' : '' }}>Any Price</option>
                            <option value="free" {{ $currentPriceRange === 'free' ? 'selected' : '' }}>Free</option>
                            <option value="0-100000" {{ $currentPriceRange === '0-100000' ? 'selected' : '' }}>Under 100K</option>
                            <option value="100000-500000" {{ $currentPriceRange === '100000-500000' ? 'selected' : '' }}>100K - 500K</option>
                            <option value="500000-1000000" {{ $currentPriceRange === '500000-1000000' ? 'selected' : '' }}>500K - 1M</option>
                            <option value="1000000+" {{ $currentPriceRange === '1000000+' ? 'selected' : '' }}>Above 1M</option>
                        </select>
                        @if(request()->has('price_min'))
                            <input type="hidden" name="price_min" value="{{ request('price_min') }}">
                        @endif
                        @if(request()->has('price_max'))
                            <input type="hidden" name="price_max" value="{{ request('price_max') }}">
                        @endif
                    </div>
                    
                    <!-- Search Button -->
                    <div>
                        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 flex items-center gap-2">
                            <i class="fas fa-search"></i>
                            <span>Search</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Categories Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Categories</h2>
            <div class="flex overflow-x-auto gap-4 pb-4 scrollbar-hide" style="scrollbar-width: none; -ms-overflow-style: none;">
                @foreach($categories as $category)
                    <a href="{{ route('events.index', ['explore' => true, 'category_id' => $category->id]) }}" 
                       class="flex-shrink-0 w-32 h-24 bg-gray-200 rounded-lg flex items-center justify-center hover:bg-gray-300 transition-colors">
                        <span class="text-sm font-medium text-gray-700 text-center px-2">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Results Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Results</h2>
            @if($events->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($events as $event)
                        <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                            <!-- Event Image -->
                            <div class="relative h-48 bg-gray-300">
                                @if($event->image)
                                    <img src="{{ Storage::url($event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-500">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-alt text-4xl mb-2"></i>
                                            <p class="text-sm">{{ $event->category->name ?? 'Event' }}</p>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Bookmark Button -->
                                @auth
                                    <button 
                                        onclick="toggleBookmarkCard({{ $event->id }}, this, event)"
                                        class="bookmark-btn-card absolute top-3 right-3 w-10 h-10 rounded-full shadow-lg transition-all duration-200 flex items-center justify-center {{ auth()->user()->hasBookmarked($event->id) ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}"
                                        data-bookmarked="{{ auth()->user()->hasBookmarked($event->id) ? 'true' : 'false' }}"
                                        title="{{ auth()->user()->hasBookmarked($event->id) ? 'Remove bookmark' : 'Bookmark this event' }}"
                                    >
                                        <i class="fas fa-bookmark"></i>
                                    </button>
                                @endauth
                            </div>
                            <!-- Event Details -->
                            <div class="p-4 bg-pink-100">
                                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                    <a href="{{ route('events.show', $event) }}" class="hover:text-red-600">
                                        {{ $event->title }}
                                    </a>
                                </h3>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p><i class="fas fa-calendar mr-1"></i>{{ $event->start_date->format('M d, Y') }}</p>
                                    <p><i class="fas fa-map-marker-alt mr-1"></i>{{ Str::limit($event->location, 30) }}</p>
                                    <p class="font-semibold text-gray-900 mt-2">
                                        @if($event->price > 0)
                                            Rp {{ number_format($event->price) }}
                                        @else
                                            Free
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="flex justify-center mt-8">
                    {{ $events->links() }}
                </div>
            @else
                <!-- No Results -->
                <div class="text-center py-12">
                    <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Events Found</h3>
                    <p class="text-gray-600 mb-6">Try adjusting your search criteria or browse all events.</p>
                    <a href="{{ route('events.index') }}" class="bg-red-600 text-white px-6 py-3 rounded-md hover:bg-red-700">
                        Browse All Events
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function toggleBookmarkCard(eventId, button, event) {
        event.preventDefault();
        event.stopPropagation();
        
        const isBookmarked = button.dataset.bookmarked === 'true';

        try {
            const response = await fetch(`/participant/bookmarks/${eventId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    category: 'saved'
                })
            });

            const data = await response.json();

            if (data.success) {
                // Toggle UI
                if (data.bookmarked) {
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
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to bookmark', 'error');
        }
    }

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
</script>

@endsection




