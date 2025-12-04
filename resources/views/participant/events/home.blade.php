@extends('participant.layout')

@section('title', 'Home - Event Connect')

@section('content')
<div class="bg-gray-100">
    <!-- Hero Section (Dark Red with Background Image) -->
    <div class="relative h-96 w-full flex items-center justify-center bg-cover bg-center overflow-hidden" style="background-image: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');">
        <!-- Overlay - Less intense red -->
        <div class="absolute inset-0 bg-gradient-to-b from-red-900/70 via-red-800/60 to-red-900/70"></div>
        <!-- Content -->
        <div class="relative z-10 text-center text-white px-4">
            <h1 class="text-4xl font-bold mb-4">Discover Amazing Events</h1>
            <p class="text-xl text-white/90">Find and join events that match your interests</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Event Recommendation Section -->
        <div class="bg-gray-100 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Event Recommendation</h2>
            <div class="relative">
                <!-- Scroll container with padding for arrows -->
                <div class="px-14">
                    <div id="recommendationScroll" class="flex overflow-x-auto gap-4 pb-4 scrollbar-hide" style="scrollbar-width: none; -ms-overflow-style: none;">
                        @foreach($events as $event)
                            <div class="flex-shrink-0 w-80 bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                                <!-- Event Image -->
                                <div class="h-48 overflow-hidden">
    @if($event->image)
        <img src="{{ Storage::url($event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
    @else
    <img src="https://picsum.photos/600/400" 
     alt="Dummy Event" class="w-full h-full object-cover">
    @endif
</div>

                                <!-- Event Details -->
                                <div class="p-4 bg-pink-100">
                                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                        <a href="{{ route('events.show', $event) }}" class="hover:text-red-600">
                                            {{ $event->title }}
                                        </a>
                                    </h3>
                                    <div class="text-sm text-gray-600">
                                        <p><i class="fas fa-calendar mr-1"></i>{{ $event->start_date->format('M d, Y') }}</p>
                                        <p><i class="fas fa-map-marker-alt mr-1"></i>{{ Str::limit($event->location, 30) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <!-- Scroll Buttons - Always visible with better positioning -->
                <button id="scrollLeftBtn" class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-xl hover:bg-gray-100 z-30 transition-all border border-gray-200" onclick="scrollRecommendation('left')" style="opacity: 1 !important;">
                    <i class="fas fa-chevron-left text-gray-700 text-lg"></i>
                </button>
                <button id="scrollRightBtn" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-xl hover:bg-gray-100 z-30 transition-all border border-gray-200" onclick="scrollRecommendation('right')" style="opacity: 1 !important;">
                    <i class="fas fa-chevron-right text-gray-700 text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Large Placeholder Section -->
        <!-- Nearest Event Section -->
        <div class="bg-gray-100 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Nearest Event</h2>
            @if($events->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($events as $event)
                        <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                            <!-- Event Image -->
                            <div class="h-48 overflow-hidden">
    @if($event->image)
        <img src="{{ Storage::url($event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
    @else
    <img src="https://picsum.photos/600/400" 
    alt="Dummy Event" class="w-full h-full object-cover">
    @endif
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
            @else
                <!-- No Results -->
                <div class="text-center py-12">
                    <i class="fas fa-calendar-alt text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Events Available</h3>
                    <p class="text-gray-600 mb-6">Check back later for upcoming events.</p>
                    <a href="{{ route('events.index', ['explore' => true]) }}" class="bg-red-600 text-white px-6 py-3 rounded-md hover:bg-red-700">
                        Explore Events
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
            // Update button visibility after scroll
            setTimeout(updateScrollButtons, 100);
        }
    }

    // Update scroll buttons visibility based on scroll position
    function updateScrollButtons() {
        const container = document.getElementById('recommendationScroll');
        const leftBtn = document.getElementById('scrollLeftBtn');
        const rightBtn = document.getElementById('scrollRightBtn');
        
        if (container && leftBtn && rightBtn) {
            // Always show buttons but adjust opacity
            if (container.scrollLeft <= 10) {
                leftBtn.style.opacity = '0.6';
                leftBtn.style.cursor = 'not-allowed';
            } else {
                leftBtn.style.opacity = '1';
                leftBtn.style.cursor = 'pointer';
            }
            
            // Show/hide right button
            const maxScroll = container.scrollWidth - container.clientWidth;
            if (container.scrollLeft >= maxScroll - 10) {
                rightBtn.style.opacity = '0.6';
                rightBtn.style.cursor = 'not-allowed';
            } else {
                rightBtn.style.opacity = '1';
                rightBtn.style.cursor = 'pointer';
            }
            
            // Ensure buttons are always visible
            leftBtn.style.display = 'flex';
            rightBtn.style.display = 'flex';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('recommendationScroll');
        if (container) {
            updateScrollButtons();
            // Update on scroll
            container.addEventListener('scroll', updateScrollButtons);
        }
    });
</script>
@endsection

