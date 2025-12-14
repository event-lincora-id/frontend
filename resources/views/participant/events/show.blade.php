@extends('participant.layout')

@section('title', $event->title . ' - Event Connect')

@section('content')
<div class="bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('events.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                        <i class="fas fa-home mr-2"></i>
                        Events
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">{{ $event->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Event Header -->
                <div class="mb-8">
                    <!-- Category Badge -->
@if($event->category)
<div class="flex items-center mb-4">
    <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $event->category->color ?? '#3B82F6' }}"></div>
    <span class="text-sm font-medium text-gray-600">{{ $event->category->name ?? 'Uncategorized' }}</span>
</div>
@endif

<!-- Event Title -->
<h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $event->title }}</h1>

<!-- Bookmark & Share Buttons -->
<div class="flex items-center gap-3 mb-4">
    @if(session('user'))
        <button id="bookmarkBtn" onclick="toggleBookmark({{ $event->id }})" class="flex items-center px-4 py-2 rounded-lg font-medium transition-all duration-200 bg-gray-100 text-gray-700 hover:bg-gray-200" data-bookmarked="false">
            <i class="fas fa-bookmark mr-2"></i>
            <span id="bookmarkText">Bookmark Event</span>
        </button>
        <button onclick="shareEvent()" class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-all duration-200">
            <i class="fas fa-share-alt mr-2"></i>Share
        </button>
    @else
        <a href="{{ route('login') }}" class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-all duration-200">
            <i class="fas fa-bookmark mr-2"></i>Login to Bookmark
        </a>
    @endif
</div>

                    <!-- Event Meta -->
                    <div class="flex flex-wrap gap-6 text-sm text-gray-600 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                            <span>{{ $event->location }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-calendar mr-2 text-gray-400"></i>
                            <span>{{ $event->start_date->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-2 text-gray-400"></i>
                            <span>{{ $event->start_date->format('H:i') }} - {{ $event->end_date->format('H:i') }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users mr-2 text-gray-400"></i>
                            <span>{{ $event->registered_count }}/{{ $event->quota }} participants</span>
                        </div>
                    </div>
                </div>

                <!-- Event Image -->
                @if($event->image_url)
                    <div class="mb-8">
                        <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-64 object-cover rounded-lg shadow-lg">
                    </div>
                @endif

                <!-- Event Description -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Event</h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed">{{ $event->description }}</p>
                    </div>
                </div>

                <!-- Event Details -->
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Event Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Date & Time</h4>
                            <p class="text-gray-700">{{ $event->start_date->format('l, F d, Y') }}</p>
                            <p class="text-gray-700">{{ $event->start_date->format('g:i A') }} - {{ $event->end_date->format('g:i A') }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Location</h4>
                            <p class="text-gray-700">{{ $event->location }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Capacity</h4>
                            <p class="text-gray-700">{{ $event->registered_count }} of {{ $event->quota }} participants</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-primary h-2 rounded-full" style="width: {{ ($event->registered_count / $event->quota) * 100 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Organizer</h4>
@if($event->organizer)
    <p class="text-gray-700">{{ $event->organizer->full_name ?? $event->organizer->name ?? 'Unknown' }}</p>
    <p class="text-sm text-gray-600">{{ $event->organizer->email ?? '' }}</p>
@else
    <p class="text-gray-700">Unknown Organizer</p>
@endif
                        </div>
                    </div>
                </div>

                <!-- Participation Status -->
                @auth
                    @if($isParticipating)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                                <div>
                                    <h3 class="text-lg font-semibold text-green-800">You're Registered!</h3>
                                    <p class="text-green-700">You're successfully registered for this event.</p>
                                    @if($userParticipation)
                                        <p class="text-sm text-green-600 mt-1">
                                            Status: <span class="font-medium">{{ ucfirst($userParticipation->status) }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Registration Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6 sticky top-8">
                    <div class="text-center mb-6">
                        <div class="text-3xl font-bold text-primary mb-2">
                            @if($event->price > 0)
                                Rp {{ number_format($event->price) }}
                            @else
                                Free
                            @endif
                        </div>
                        <p class="text-gray-600">per participant</p>
                    </div>

                    <!-- Availability Status -->
                    @if($event->registered_count >= $event->quota)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                <span class="text-red-800 font-medium">Event Full</span>
                            </div>
                        </div>
                    @elseif($event->end_date < now())
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-gray-600 mr-2"></i>
                                <span class="text-gray-800 font-medium">Event Ended</span>
                            </div>
                        </div>
                    @elseif($event->start_date < now())
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-circle-notch fa-spin text-blue-600 mr-2"></i>
                                <span class="text-blue-800 font-medium">Event Started</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                <span class="text-green-800 font-medium">Available</span>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
@if(session('user'))
    @if($isParticipating ?? false)
        <!-- Already Registered -->
        <a href="{{ route('attendance.scanner') }}" class="w-full bg-green-600 text-white py-3 rounded-lg font-medium hover:bg-green-700 transition-colors duration-200 mb-3 inline-block text-center">
            <i class="fas fa-check-circle mr-2"></i>Already Registered
        </a>
    @elseif($event->quota > 0 && $event->registered_count >= $event->quota)
        <button class="w-full bg-gray-400 text-white py-3 rounded-lg font-medium mb-3" disabled>
            <i class="fas fa-times mr-2"></i>Event Full
        </button>
    @elseif($event->end_date < now())
        <button class="w-full bg-gray-400 text-white py-3 rounded-lg font-medium mb-3" disabled>
            <i class="fas fa-check-circle mr-2"></i>Event Ended
        </button>
    @elseif($event->start_date < now())
        <button class="w-full bg-gray-400 text-white py-3 rounded-lg font-medium mb-3" disabled>
            <i class="fas fa-clock mr-2"></i>Event Started
        </button>
    @else
        <!-- Join Event Button -->
        @if($event->price > 0)
            <!-- Paid Event - Direct Payment -->
            <button onclick="handleJoinEvent()" id="joinEventBtn" data-event-id="{{ $event->id }}" data-event-price="{{ $event->price }}" class="w-full bg-red-600 text-white py-3 rounded-lg font-medium hover:bg-red-700 transition-colors duration-200 mb-3 disabled:bg-gray-400 disabled:cursor-not-allowed">
                <span id="joinBtnText">
                    <i class="fas fa-credit-card mr-2"></i>Pay Rp {{ number_format($event->price) }}
                </span>
                <span id="joinBtnLoading" class="hidden">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                </span>
            </button>
        @else
            <!-- Free Event - Direct Registration -->
            <form action="{{ route('events.register', $event->id) }}" method="POST" id="joinEventForm" onsubmit="handleJoinSubmit(event)">
                @csrf
                <button type="submit" id="joinEventBtn" class="w-full bg-red-600 text-white py-3 rounded-lg font-medium hover:bg-red-700 transition-colors duration-200 mb-3 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <span id="joinBtnText">
                        <i class="fas fa-plus mr-2"></i>Join Event (Free)
                    </span>
                    <span id="joinBtnLoading" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Joining...
                    </span>
                </button>
            </form>
        @endif
    @endif
@else
    <a href="{{ route('login') }}" class="w-full bg-red-600 text-white py-3 rounded-lg font-medium hover:bg-red-700 transition-colors duration-200 mb-3 inline-block text-center">
        <i class="fas fa-sign-in-alt mr-2"></i>Login to Join
    </a>
@endif

<a href="{{ route('events.index') }}" class="w-full bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors duration-200 inline-block text-center">
    <i class="fas fa-arrow-left mr-2"></i>Back to Events
</a>
                </div>

                <!-- Organizer Info -->
<div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Organizer</h3>
    @if($event->organizer)
        <div class="flex items-center">
            <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                {{ strtoupper(substr($event->organizer->name ?? $event->organizer->full_name ?? 'O', 0, 1)) }}
            </div>
            <div>
                <h4 class="font-semibold text-gray-900">{{ $event->organizer->full_name ?? $event->organizer->name ?? 'Unknown' }}</h4>
                <p class="text-sm text-gray-600">{{ $event->organizer->email ?? '' }}</p>
                @if(isset($event->organizer->bio) && $event->organizer->bio)
                    <p class="text-sm text-gray-700 mt-2">{{ Str::limit($event->organizer->bio, 100) }}</p>
                @endif
            </div>
        </div>
    @else
        <p class="text-gray-600">No organizer information available</p>
    @endif
</div>
                    </div>
                </div>

                <!-- Share Event -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Share This Event</h3>
                    <div class="flex space-x-3">
                        <button class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-md hover:bg-blue-700 transition-colors duration-200">
                            <i class="fab fa-facebook mr-1"></i>Facebook
                        </button>
                        <button class="flex-1 bg-blue-400 text-white py-2 px-3 rounded-md hover:bg-blue-500 transition-colors duration-200">
                            <i class="fab fa-twitter mr-1"></i>Twitter
                        </button>
                        <button class="flex-1 bg-green-600 text-white py-2 px-3 rounded-md hover:bg-green-700 transition-colors duration-200">
                            <i class="fab fa-whatsapp mr-1"></i>WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Get access token from localStorage or session
    const accessToken = localStorage.getItem('access_token') || '{{ auth()->user() ? auth()->user()->createToken("web")->plainTextToken : "" }}';

    function joinEvent() {
        if (confirm('Are you sure you want to join this event?')) {
            // Join free event
            fetch('/api/participants/join/{{ $event->id }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + accessToken,
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Successfully joined the event!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while joining the event.');
            });
        }
    }

    // Handle join event - Direct payment for paid events
    async function handleJoinEvent() {
        const btn = document.getElementById('joinEventBtn');
        const btnText = document.getElementById('joinBtnText');
        const btnLoading = document.getElementById('joinBtnLoading');
        const eventId = btn.dataset.eventId;

        try {
            // Show loading state
            btn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');

            console.log('Creating payment for event:', eventId);

            // Create payment via frontend API (always uses Xendit Invoice)
            const response = await fetch(`/events/${eventId}/payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    payment_method: 'invoice'
                })
            });

            const data = await response.json();
            console.log('Payment response:', data);

            if (!response.ok) {
                throw new Error(data.message || 'Failed to create payment');
            }

            if (data.success && data.data && data.data.payment_url) {
                // Show success message
                showToast('Redirecting to payment...', 'success');

                // Small delay for better UX
                setTimeout(() => {
                    // Redirect to Xendit invoice page
                    window.location.href = data.data.payment_url;
                }, 500);
            } else {
                throw new Error(data.message || 'No payment URL received');
            }

        } catch (error) {
            console.error('Payment error:', error);
            showToast(error.message || 'Failed to process payment', 'error');

            // Restore button state
            btn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
        }
    }

    // Share functionality
    document.querySelectorAll('button').forEach(button => {
        if (button.textContent.includes('Facebook') || button.textContent.includes('Twitter') || button.textContent.includes('WhatsApp')) {
            button.addEventListener('click', function() {
                const url = window.location.href;
                const title = '{{ $event->title }}';
                
                if (this.textContent.includes('Facebook')) {
                    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
                } else if (this.textContent.includes('Twitter')) {
                    window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank');
                } else if (this.textContent.includes('WhatsApp')) {
                    window.open(`https://wa.me/?text=${encodeURIComponent(title + ' - ' + url)}`, '_blank');
                }
            });
        }
    });
</script>

<!-- Bookmark JavaScript -->
<script>
    // Bookmark Manager dengan localStorage
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

    // Make function globally accessible for onclick handler
    window.toggleBookmark = function(eventId) {
        console.log('Detail bookmark clicked!', eventId);
        const btn = document.getElementById('bookmarkBtn');
        const text = document.getElementById('bookmarkText');
        const isBookmarked = BookmarkManager.isBookmarked(eventId);

        try {
            if (isBookmarked) {
                // Remove bookmark
                BookmarkManager.removeBookmark(eventId);
                btn.classList.remove('bg-red-600', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                text.textContent = 'Bookmark Event';
                btn.dataset.bookmarked = 'false';
                showToast('Bookmark removed', 'info');
            } else {
                // Add bookmark
                BookmarkManager.addBookmark(eventId);
                btn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                btn.classList.add('bg-red-600', 'text-white');
                text.textContent = 'Bookmarked';
                btn.dataset.bookmarked = 'true';
                showToast('Event bookmarked! 🎉', 'success');
            }
        } catch (error) {
            console.error('Bookmark toggle error:', error);
            showToast('Failed to toggle bookmark', 'error');
        }
    };

    // Check bookmark status on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing detail bookmark...');
        const eventId = {{ $event->id }};
        const btn = document.getElementById('bookmarkBtn');
        const text = document.getElementById('bookmarkText');
        
        if (BookmarkManager.isBookmarked(eventId)) {
            btn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            btn.classList.add('bg-red-600', 'text-white');
            text.textContent = 'Bookmarked';
            btn.dataset.bookmarked = 'true';
        }
    });

    function shareEvent() {
        const url = window.location.href;
        const title = "{{ $event->title }}";
        
        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            }).catch(err => console.log('Error sharing:', err));
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(url).then(() => {
                showToast('Link copied to clipboard!', 'success');
            });
        }
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white shadow-lg z-50 animate-slide-in ${
            type === 'success' ? 'bg-green-600' : 
            type === 'error' ? 'bg-red-600' : 
            'bg-blue-600'
        }`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s, transform 0.3s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Handle join event form submission
    function handleJoinSubmit(event) {
        const btn = document.getElementById('joinEventBtn');
        const btnText = document.getElementById('joinBtnText');
        const btnLoading = document.getElementById('joinBtnLoading');
        
        // Disable button and show loading
        btn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        
        // Form will submit normally, button state will reset on page reload
        return true;
    }
</script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('{{ session('success') }}', 'success');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('{{ session('error') }}', 'error');
    });
</script>
@endif

@if(session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('{{ session('info') }}', 'info');
    });
</script>
@endif

<style>
    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
</style>
@endpush
