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
                    <div class="flex items-center mb-4">
                        <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $event->category->color ?? '#3B82F6' }}"></div>
                        <span class="text-sm font-medium text-gray-600">{{ $event->category->name ?? 'Uncategorized' }}</span>
                    </div>

                    <!-- Event Title -->
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $event->title }}</h1>

                    <!-- TOMBOL BOOKMARK -->
                    <div class="flex items-center gap-3 mb-4">
                        @auth
                            <button 
                                id="bookmarkBtn" 
                                onclick="toggleBookmark({{ $event->id }})"
                                class="bookmark-btn flex items-center px-4 py-2 rounded-lg font-medium transition-all duration-200 {{ auth()->user()->hasBookmarked($event->id) ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                data-bookmarked="{{ auth()->user()->hasBookmarked($event->id) ? 'true' : 'false' }}"
                            >
                                <i class="fas fa-bookmark mr-2"></i>
                                <span id="bookmarkText">{{ auth()->user()->hasBookmarked($event->id) ? 'Bookmarked' : 'Bookmark Event' }}</span>
                            </button>
                            
                            <!-- Share Button -->
                            <button onclick="shareEvent()" class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-all duration-200">
                                <i class="fas fa-share-alt mr-2"></i>
                                Share
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-all duration-200">
                                <i class="fas fa-bookmark mr-2"></i>
                                Login to Bookmark
                            </a>
                        @endauth
                    </div>
                    <!-- AKHIR TOMBOL BOOKMARK -->

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
                @if($event->image)
                    <div class="mb-8">
                        <img src="{{ Storage::url($event->image) }}" alt="{{ $event->title }}" class="w-full h-64 object-cover rounded-lg">
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
                            <p class="text-gray-700">{{ $event->organizer->full_name ?? $event->organizer->name }}</p>
                            <p class="text-sm text-gray-600">{{ $event->organizer->email }}</p>
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
                    @elseif($event->start_date < now())
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-gray-600 mr-2"></i>
                                <span class="text-gray-800 font-medium">Event Started</span>
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
                    @auth
                        @if($isParticipating)
                            <button class="w-full bg-gray-500 text-white py-3 rounded-lg font-medium mb-3" disabled>
                                <i class="fas fa-check mr-2"></i>Already Registered
                            </button>
                        @elseif($event->registered_count >= $event->quota)
                            <button class="w-full bg-gray-400 text-white py-3 rounded-lg font-medium mb-3" disabled>
                                <i class="fas fa-times mr-2"></i>Event Full
                            </button>
                        @elseif($event->start_date < now())
                            <button class="w-full bg-gray-400 text-white py-3 rounded-lg font-medium mb-3" disabled>
                                <i class="fas fa-clock mr-2"></i>Event Started
                            </button>
                        @else
                            @if($event->price > 0)
                                <button class="w-full bg-primary text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200 mb-3" onclick="showPaymentModal()">
                                    <i class="fas fa-credit-card mr-2"></i>Join Event (Paid)
                                </button>
                            @else
                                <button class="w-full bg-primary text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200 mb-3" onclick="joinEvent()">
                                    <i class="fas fa-plus mr-2"></i>Join Event (Free)
                                </button>
                            @endif
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="w-full bg-primary text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200 mb-3 inline-block text-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login to Join
                        </a>
                    @endauth

                    <a href="{{ route('events.index') }}" class="w-full bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors duration-200 inline-block text-center">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Events
                    </a>
                </div>

                <!-- Organizer Info -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Organizer</h3>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                            {{ substr($event->organizer->name ?? 'O', 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $event->organizer->full_name ?? $event->organizer->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $event->organizer->email }}</p>
                            @if($event->organizer->bio)
                                <p class="text-sm text-gray-700 mt-2">{{ Str::limit($event->organizer->bio, 100) }}</p>
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

<!-- Payment Method Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Choose Payment Method</h3>
                <button onclick="hidePaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Event Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-gray-900">{{ $event->title }}</h4>
                <p class="text-sm text-gray-600">{{ $event->start_date->format('M d, Y H:i') }}</p>
                <p class="text-lg font-bold text-primary">Rp {{ number_format($event->price) }}</p>
            </div>

            <!-- Payment Methods -->
            <div class="space-y-3">
                <!-- Invoice Payment -->
                <button onclick="createPayment('invoice')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-credit-card text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">Credit Card</h5>
                        <p class="text-sm text-gray-600">Visa, Mastercard, JCB</p>
                    </div>
                </button>

                <!-- Virtual Account -->
                <button onclick="showBankSelection()" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-university text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">Virtual Account</h5>
                        <p class="text-sm text-gray-600">BCA, BNI, BRI, Mandiri</p>
                    </div>
                </button>

                <!-- E-Wallet -->
                <button onclick="showEWalletSelection()" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-mobile-alt text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">E-Wallet</h5>
                        <p class="text-sm text-gray-600">OVO, DANA, LinkAja, ShopeePay</p>
                    </div>
                </button>
            </div>

            <!-- Loading State -->
            <div id="paymentLoading" class="hidden text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-sm text-gray-600 mt-2">Processing payment...</p>
            </div>
        </div>
    </div>
</div>

<!-- Bank Selection Modal -->
<div id="bankModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select Bank</h3>
                <button onclick="hideBankModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-3">
                <button onclick="createPayment('virtual_account', 'BCA')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-university text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">BCA</h5>
                        <p class="text-sm text-gray-600">Bank Central Asia</p>
                    </div>
                </button>

                <button onclick="createPayment('virtual_account', 'BNI')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-red-500 hover:bg-red-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-university text-2xl text-red-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">BNI</h5>
                        <p class="text-sm text-gray-600">Bank Negara Indonesia</p>
                    </div>
                </button>

                <button onclick="createPayment('virtual_account', 'BRI')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-university text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">BRI</h5>
                        <p class="text-sm text-gray-600">Bank Rakyat Indonesia</p>
                    </div>
                </button>

                <button onclick="createPayment('virtual_account', 'MANDIRI')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-university text-2xl text-yellow-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">Mandiri</h5>
                        <p class="text-sm text-gray-600">Bank Mandiri</p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- E-Wallet Selection Modal -->
<div id="ewalletModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select E-Wallet</h3>
                <button onclick="hideEWalletModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-3">
                <button onclick="createPayment('ewallet', 'OVO')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-mobile-alt text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">OVO</h5>
                        <p class="text-sm text-gray-600">Digital Wallet</p>
                    </div>
                </button>

                <button onclick="createPayment('ewallet', 'DANA')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-mobile-alt text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">DANA</h5>
                        <p class="text-sm text-gray-600">Digital Wallet</p>
                    </div>
                </button>

                <button onclick="createPayment('ewallet', 'LINKAJA')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-mobile-alt text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">LinkAja</h5>
                        <p class="text-sm text-gray-600">Digital Wallet</p>
                    </div>
                </button>

                <button onclick="createPayment('ewallet', 'SHOPEEPAY')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-mobile-alt text-2xl text-orange-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">ShopeePay</h5>
                        <p class="text-sm text-gray-600">Digital Wallet</p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
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

    function showPaymentModal() {
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function hidePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    function showBankSelection() {
        hidePaymentModal();
        document.getElementById('bankModal').classList.remove('hidden');
    }

    function hideBankModal() {
        document.getElementById('bankModal').classList.add('hidden');
        showPaymentModal();
    }

    function showEWalletSelection() {
        hidePaymentModal();
        document.getElementById('ewalletModal').classList.remove('hidden');
    }

    function hideEWalletModal() {
        document.getElementById('ewalletModal').classList.add('hidden');
        showPaymentModal();
    }

    function createPayment(paymentMethod, provider = null) {
        // Show loading
        document.getElementById('paymentLoading').classList.remove('hidden');
        
        // Hide all modals
        hidePaymentModal();
        hideBankModal();
        hideEWalletModal();

        // Prepare payment data
        const paymentData = {
            event_id: {{ $event->id }},
            payment_method: paymentMethod
        };

        if (paymentMethod === 'virtual_account' && provider) {
            paymentData.bank_code = provider;
        } else if (paymentMethod === 'ewallet' && provider) {
            paymentData.ewallet_type = provider;
        }

        // Create payment
        fetch('/api/payments/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + accessToken,
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(paymentData)
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('paymentLoading').classList.add('hidden');
            
            if (data.success) {
                // Redirect to payment URL
                if (data.data.payment_url) {
                    // Direct redirect to Xendit payment page
                    window.location.href = data.data.payment_url;
                } else {
                    // If no payment URL (free event), show success and reload
                    alert('Payment created successfully!');
                    location.reload();
                }
            } else {
                // Show detailed error message but don't prevent retry
                let errorMessage = 'Error: ' + data.message;
                if (data.errors) {
                    errorMessage += '\n\nDetails:';
                    for (const [field, errors] of Object.entries(data.errors)) {
                        errorMessage += '\n• ' + field + ': ' + errors.join(', ');
                    }
                }
                
                // If payment URL exists in error response, still redirect
                if (data.data && data.data.payment_url) {
                    console.warn('Error but payment URL exists, redirecting:', errorMessage);
                    window.location.href = data.data.payment_url;
                } else {
                    alert(errorMessage);
                }
            }
        })
        .catch(error => {
            document.getElementById('paymentLoading').classList.add('hidden');
            console.error('Error:', error);
            
            // Try to get payment URL from error response if available
            if (error.response && error.response.data && error.response.data.payment_url) {
                console.warn('Error but payment URL exists, redirecting:', error.response.data.payment_url);
                window.location.href = error.response.data.payment_url;
            } else {
                alert('An error occurred while creating payment. Please try again.');
            }
        });
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

    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('fixed')) {
            hidePaymentModal();
            hideBankModal();
            hideEWalletModal();
        }
    });
</script>

<!-- Bookmark JavaScript -->
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function toggleBookmark(eventId) {
        const btn = document.getElementById('bookmarkBtn');
        const text = document.getElementById('bookmarkText');
        const isBookmarked = btn.dataset.bookmarked === 'true';

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
                    btn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                    btn.classList.add('bg-red-600', 'text-white');
                    text.textContent = 'Bookmarked';
                    btn.dataset.bookmarked = 'true';
                    showToast('Event bookmarked successfully! 🎉', 'success');
                } else {
                    btn.classList.remove('bg-red-600', 'text-white');
                    btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                    text.textContent = 'Bookmark Event';
                    btn.dataset.bookmarked = 'false';
                    showToast('Bookmark removed', 'info');
                }
            } else {
                showToast(data.message || 'Failed to bookmark event', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to bookmark event', 'error');
        }
    }

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
</script>

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
@endsection
