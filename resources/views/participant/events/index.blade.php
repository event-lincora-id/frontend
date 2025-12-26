@extends('participant.layout')

@section('title', 'Browse Events - Event Connect')

@push('head-scripts')
<!-- Removed old localStorage-based bookmark code - now using API-based bookmarks -->
@endpush

@section('content')
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
            <form method="GET" action="{{ route('events.index') }}" id="searchForm">
                <!-- Main Search Bar -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-4">
                    <div class="flex flex-col md:flex-row gap-3">
                        <!-- Large Search Input -->
                        <div class="flex-1">
                            <div class="relative">
                                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
                                <input type="text"
                                       name="search"
                                       value="{{ request('search') ?? '' }}"
                                       placeholder="Search events by name, location, or organizer..."
                                       class="w-full pl-12 pr-4 py-4 text-lg border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all">
                            </div>
                        </div>

                        <!-- Search Button -->
                        <button type="submit"
                                class="bg-red-600 text-white px-8 py-4 rounded-lg hover:bg-red-700 font-semibold text-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-search"></i>
                            <span class="hidden sm:inline">Search</span>
                        </button>

                        <!-- Advanced Filters Toggle Button -->
                        <button type="button"
                                onclick="toggleAdvancedFilters()"
                                class="bg-gray-100 text-gray-700 px-6 py-4 rounded-lg hover:bg-gray-200 font-medium flex items-center justify-center gap-2 transition-all">
                            <i class="fas fa-sliders-h"></i>
                            <span class="hidden sm:inline">Filters</span>
                            <i class="fas fa-chevron-down transition-transform" id="filterIcon"></i>
                        </button>

                        <!-- Clear Button (shown when filters active) -->
                        @if(request('search') || request('category_id') || request('is_paid') || request('date_filter') || request('sort_by'))
                        <a href="{{ route('events.index') }}"
                           class="bg-gray-200 text-gray-700 px-6 py-4 rounded-lg hover:bg-gray-300 flex items-center justify-center gap-2 transition-all">
                            <i class="fas fa-times"></i>
                            <span class="hidden sm:inline">Clear</span>
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Advanced Filters Section (Collapsible) -->
                <div id="advancedFilters" class="bg-gray-50 rounded-xl shadow-sm p-6 mb-4 hidden">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-filter"></i>
                        ADVANCED FILTERS
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-folder mr-1 text-gray-400"></i>
                                Category
                            </label>
                            <select name="category_id"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tag mr-1 text-gray-400"></i>
                                Price
                            </label>
                            <select name="is_paid"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    onchange="this.form.submit()">
                                <option value="">All Events</option>
                                <option value="0" {{ request('is_paid') === '0' ? 'selected' : '' }}>Free Only</option>
                                <option value="1" {{ request('is_paid') === '1' ? 'selected' : '' }}>Paid Only</option>
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1 text-gray-400"></i>
                                Date Range
                            </label>
                            <select name="date_filter"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    onchange="this.form.submit()">
                                <option value="">All Dates</option>
                                <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>This Month</option>
                            </select>
                        </div>

                        <!-- Sort Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-sort mr-1 text-gray-400"></i>
                                Sort By
                            </label>
                            <select name="sort_by"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    onchange="this.form.submit()">
                                <option value="start_date" {{ request('sort_by') === 'start_date' || !request('sort_by') ? 'selected' : '' }}>Date (Earliest)</option>
                                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Newest First</option>
                                <option value="popularity" {{ request('sort_by') === 'popularity' ? 'selected' : '' }}>Most Popular</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Active Filters Info -->
                @if(request('search') || request('category_id') || request('is_paid') || request('date_filter') || request('sort_by'))
                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-gray-600">
                    <span class="font-medium">Active filters:</span>
                    @if(request('search'))
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full">
                            Search: "{{ request('search') }}"
                        </span>
                    @endif
                    @if(request('category_id'))
                        @php
                            $selectedCategory = $categories->firstWhere('id', request('category_id'));
                        @endphp
                        @if($selectedCategory)
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                                Category: {{ $selectedCategory->name }}
                            </span>
                        @endif
                    @endif
                    @if(request('is_paid') !== null && request('is_paid') !== '')
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full">
                            Price: {{ request('is_paid') === '0' ? 'Free Only' : 'Paid Only' }}
                        </span>
                    @endif
                    @if(request('date_filter'))
                        <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full">
                            Date: {{ ucwords(str_replace('_', ' ', request('date_filter'))) }}
                        </span>
                    @endif
                </div>
                @endif
            </form>
        </div>

       <!-- Results Section -->
<div class="mb-8">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-900">
            @if($currentSearch || $currentCategory)
                Search Results
            @else
                All Events
            @endif
            <span class="text-gray-500 text-lg ml-2">({{ $events->count() }} events)</span>
        </h2>
    </div>
    
    @if(isset($error))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
            <p>{{ $error }}</p>
        </div>
    @endif
    
    @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
                <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition-all duration-300">
                    <!-- Event Image -->
                    <div class="relative h-48 bg-gray-300">
                        @if($event->image_url)
                            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-white text-6xl opacity-30"></i>
                            </div>
                        @endif
                        
                        <!-- Category Badge -->
                        @if($event->category)
                            <div class="absolute top-3 left-3">
                                <span class="bg-red-600 text-white text-xs font-semibold px-3 py-1 rounded-full shadow-lg">
                                    {{ $event->category->name }}
                                </span>
                            </div>
                        @endif
                        
                        <!-- Price Badge -->
                        <div class="absolute bottom-3 right-3">
                            @if($event->price > 0)
                                <span class="bg-yellow-400 text-gray-900 text-sm font-bold px-3 py-1 rounded-full shadow-lg">
                                    Rp {{ number_format($event->price, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="bg-green-500 text-white text-sm font-bold px-3 py-1 rounded-full shadow-lg">
                                    FREE
                                </span>
                            @endif
                        </div>
                        
                        <!-- Bookmark Button -->
                        @if(session('user'))
                            <button
                                onclick="event.preventDefault(); event.stopPropagation(); toggleBookmarkCard({{ $event->id }}, this);"
                                class="bookmark-btn-card absolute top-3 right-3 w-10 h-10 rounded-full shadow-lg transition-all duration-200 flex items-center justify-center bg-white text-gray-700 hover:bg-gray-100"
                                data-event-id="{{ $event->id }}"
                                data-bookmarked="false"
                                title="Bookmark this event"
                            >
                                <i class="fas fa-bookmark"></i>
                            </button>
                        @endif
                    </div>
                    
                    <!-- Event Details -->
                    <div class="p-5">
                        <h3 class="font-bold text-lg text-gray-900 mb-3 line-clamp-2 hover:text-red-600 transition-colors">
                            <a href="{{ route('events.show', $event->id) }}">
                                {{ $event->title }}
                            </a>
                        </h3>
                        
                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt w-5 text-red-500"></i>
                                <span>{{ $event->start_date->format('D, M d, Y') }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock w-5 text-red-500"></i>
                                <span>{{ $event->start_date->format('H:i') }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt w-5 text-red-500"></i>
                                <span class="line-clamp-1">{{ $event->location }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-users w-5 text-red-500"></i>
                                <span>{{ $event->registered_count ?? 0 }} / {{ $event->quota ?? 0 }} participants</span>
                            </div>
                        </div>
                        
                        <a href="{{ route('events.show', $event->id) }}" 
                           class="block w-full text-center bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors font-medium">
                            View Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- No Results -->
        <div class="text-center py-16">
            <div class="max-w-md mx-auto">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    @if(request('search') || request('category'))
                        <i class="fas fa-search text-5xl text-gray-400"></i>
                    @else
                        <i class="fas fa-calendar-times text-5xl text-gray-400"></i>
                    @endif
                </div>
                
                @if(request('search') || request('category'))
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No Events Match Your Search</h3>
                    <p class="text-gray-600 mb-6">We couldn't find any events matching "{{ request('search') }}"
                        @if(request('category')) in {{ $categories->firstWhere('id', request('category'))->name ?? 'this category' }} @endif
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('events.index') }}" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors font-medium">
                            <i class="fas fa-times mr-2"></i>Clear Filters
                        </a>
                        <button onclick="history.back()" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                            <i class="fas fa-arrow-left mr-2"></i>Go Back
                        </button>
                    </div>
                @else
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No Events Available Yet</h3>
                    <p class="text-gray-600 mb-6">There are currently no published events. Check back soon!</p>
                    
                    <!-- Info Box for Organizers -->
                    @if(session('user') && (session('user')['role'] === 'admin' || session('user')['role'] === 'organizer'))
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 text-left">
                            <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                                <i class="fas fa-lightbulb mr-2"></i>
                                You can create events!
                            </h4>
                            <p class="text-sm text-blue-800 mb-4">As an organizer, you can create and publish events for the community.</p>
                            <a href="{{ route('admin.events') }}" class="inline-flex items-center text-sm font-medium text-blue-700 hover:text-blue-900">
                                Go to Admin Dashboard <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    @endif
                    
                    <a href="{{ route('home') }}" class="inline-block bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition-colors font-medium shadow-md">
                        <i class="fas fa-home mr-2"></i>Back to Home
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
    </div>
</div>
@endsection

@push('scripts')
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

    // Toggle Advanced Filters
    function toggleAdvancedFilters() {
        const filters = document.getElementById('advancedFilters');
        const icon = document.getElementById('filterIcon');

        if (filters.classList.contains('hidden')) {
            filters.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            filters.classList.add('hidden');
            icon.classList.remove('rotate-180');
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

@endpush




