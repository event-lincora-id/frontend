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

        @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if(session('info'))
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg">
            <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
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
            <!-- Events with Tabs (Upcoming/Past) -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <!-- Tabs Header -->
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button onclick="switchTab('upcoming')" id="tab-upcoming" class="tab-button active flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 border-red-600 text-red-600">
                                <i class="fas fa-calendar"></i>
                                Upcoming
                                <span class="bg-red-100 text-red-600 px-2 py-0.5 rounded-full text-xs">{{ $upcomingEvents->count() }}</span>
                            </button>
                            <button onclick="switchTab('past')" id="tab-past" class="tab-button flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                <i class="fas fa-history"></i>
                                Past Events
                                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">{{ $pastEvents->count() }}</span>
                            </button>
                        </nav>
                    </div>

                    <!-- Upcoming Events Tab Content -->
                    <div id="content-upcoming" class="tab-content p-6">
                        <div class="space-y-4">
                            @forelse ($upcomingEvents as $participant)
                                @php
                                    $event = $participant->event ?? null;
                                    if (!$event) continue;
                                    $startDate = isset($event->start_date) ? \Carbon\Carbon::parse($event->start_date) : null;
                                    $status = $participant->status ?? 'registered';
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
                                            @if($status === 'attended')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                    <i class="fas fa-check-circle mr-1"></i>Attended
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i>Registered
                                                </span>
                                            @endif

                                            @if($status === 'attended')
                                                @php
                                                    // Defensive check: ensure $userFeedbacks exists and is an array
                                                    $hasFeedback = isset($userFeedbacks) && is_array($userFeedbacks) && isset($userFeedbacks[$event->id ?? 0]);
                                                @endphp
                                                @if($hasFeedback)
                                                    <a href="{{ route('participant.feedback.certificate.download', $event->id ?? 0) }}" class="inline-flex items-center text-green-600 hover:text-green-700 text-sm font-medium">
                                                        <i class="fas fa-certificate mr-1"></i>Download Certificate
                                                    </a>
                                                @else
                                                    <a href="{{ route('participant.feedback.create', $event->id ?? 0) }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 text-sm font-medium">
                                                        <i class="fas fa-comment-dots mr-1"></i>Give Feedback
                                                    </a>
                                                @endif
                                            @endif

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

                    <!-- Past Events Tab Content -->
                    <div id="content-past" class="tab-content hidden p-6">
                        <div class="space-y-4">
                            @forelse ($pastEvents as $participant)
                                @php
                                    $event = $participant->event ?? null;
                                    if (!$event) continue;
                                    $startDate = isset($event->start_date) ? \Carbon\Carbon::parse($event->start_date) : null;
                                    $status = $participant->status ?? 'registered';
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
                                            @if($status === 'attended')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>Attended
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                    <i class="fas fa-times-circle mr-1"></i>Not Attended
                                                </span>
                                            @endif

                                            @if($status === 'attended')
                                                @php
                                                    // Defensive check: ensure $userFeedbacks exists and is an array
                                                    $hasFeedback = isset($userFeedbacks) && is_array($userFeedbacks) && isset($userFeedbacks[$event->id ?? 0]);
                                                @endphp
                                                @if($hasFeedback)
                                                    <a href="{{ route('participant.feedback.certificate.download', $event->id ?? 0) }}" class="inline-flex items-center text-green-600 hover:text-green-700 text-sm font-medium">
                                                        <i class="fas fa-certificate mr-1"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('participant.feedback.create', $event->id ?? 0) }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 text-sm font-medium">
                                                        <i class="fas fa-comment-dots mr-1"></i>Give Feedback
                                                    </a>
                                                @endif
                                            @endif

                                            <a href="{{ route('events.show', $event->id ?? 0) }}" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                                View Details <i class="fas fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-history text-3xl text-gray-400"></i>
                                    </div>
                                    <p class="text-gray-500">No past events yet.</p>
                                </div>
                            @endforelse
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
                            <a href="{{ route('attendance.scanner') }}" class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors shadow-md">
                                <i class="fas fa-qrcode mr-2"></i>Scan QR Code
                            </a>
                            <a href="{{ route('participant.profile') }}" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-user mr-2"></i>Edit Profile
                            </a>
                            <a href="#" onclick="switchTab('past'); return false;" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-certificate mr-2"></i>My Certificates
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Payment History -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden mt-6">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Payment History</h3>
                </div>
                <div class="p-6">
                    @if(isset($paymentHistory) && $paymentHistory->count() > 0)
                        <!-- Warning Message -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                                <span class="text-sm text-yellow-800">Showing 3 most recent payments</span>
                            </div>
                        </div>

                        <!-- Payment Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($paymentHistory->take(3) as $payment)
                                        @php
                                            $event = $payment->event ?? null;
                                            $paymentInfo = $payment->payment ?? null;
                                            $participationId = $payment->id ?? null;

                                            if (!$event || !$paymentInfo) continue;

                                            $status = strtolower($paymentInfo->status ?? 'pending');
                                            $amount = $paymentInfo->amount ?? ($event->price ?? 0);
                                            $isPaid = $paymentInfo->is_paid ?? false;
                                        @endphp
                                        <tr>
                                            <!-- Event Column -->
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <div class="text-sm font-medium text-gray-900">{{ $event->title ?? 'Unknown Event' }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        <i class="fas fa-calendar mr-1"></i>
                                                        {{ isset($event->start_date) ? \Carbon\Carbon::parse($event->start_date)->format('d M Y') : 'N/A' }}
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Amount Column -->
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($amount, 0, ',', '.') }}</div>
                                            </td>

                                            <!-- Status Column -->
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                @if($status === 'paid')
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i>Paid
                                                    </span>
                                                @elseif($status === 'pending')
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-clock mr-1"></i>Pending
                                                    </span>
                                                @elseif($status === 'failed')
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                        <i class="fas fa-times-circle mr-1"></i>Failed
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                        {{ ucfirst($status) }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Actions Column -->
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                @if($status === 'pending' && $participationId)
                                                    <div class="flex items-center gap-2">
                                                        <button onclick="showPaymentModalForParticipant({{ $participationId }}, '{{ $event->title ?? '' }}', {{ $amount }})"
                                                                class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                                                            <i class="fas fa-credit-card mr-1"></i>Confirm
                                                        </button>
                                                        <button onclick="cancelPayment({{ $participationId }})"
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors">
                                                            <i class="fas fa-times mr-1"></i>Cancel
                                                        </button>
                                                    </div>
                                                @elseif($status === 'paid')
                                                    <span class="text-xs text-gray-500">No action needed</span>
                                                @else
                                                    <span class="text-xs text-gray-500">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-receipt text-3xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 mb-4">No payment history yet.</p>
                            <a href="{{ route('events.index') }}" class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                <i class="fas fa-search mr-2"></i>Browse Paid Events
                            </a>
                        </div>
                    @endif
                </div>
            </div>


        </div>
    </div>
</div>

<!-- Payment Method Modal for Dashboard -->
<div id="dashboardPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Choose Payment Method</h3>
                <button onclick="hideDashboardPaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Event Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h4 id="paymentEventTitle" class="font-semibold text-gray-900"></h4>
                <p id="paymentEventAmount" class="text-lg font-bold text-red-600"></p>
            </div>

            <!-- Payment Methods -->
            <div class="space-y-3">
                <!-- Invoice Payment -->
                <button onclick="createPaymentForParticipant('invoice')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-credit-card text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">Credit Card</h5>
                        <p class="text-sm text-gray-600">Visa, Mastercard, JCB</p>
                    </div>
                </button>

                <!-- Virtual Account -->
                <button onclick="showDashboardBankSelection()" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-university text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4 text-left">
                        <h5 class="font-medium text-gray-900">Virtual Account</h5>
                        <p class="text-sm text-gray-600">BCA, BNI, BRI, Mandiri</p>
                    </div>
                </button>

                <!-- E-Wallet -->
                <button onclick="showDashboardEWalletSelection()" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
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
            <div id="dashboardPaymentLoading" class="hidden text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-sm text-gray-600 mt-2">Processing payment...</p>
            </div>
        </div>
    </div>
</div>

<!-- Bank Selection Modal -->
<div id="dashboardBankModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select Bank</h3>
                <button onclick="hideDashboardBankModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-3">
                <button onclick="createPaymentForParticipant('virtual_account', 'BCA')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                    <i class="fas fa-university text-2xl text-blue-600 mr-4"></i>
                    <div class="text-left">
                        <h5 class="font-medium text-gray-900">BCA</h5>
                        <p class="text-sm text-gray-600">Bank Central Asia</p>
                    </div>
                </button>

                <button onclick="createPaymentForParticipant('virtual_account', 'BNI')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-red-500 hover:bg-red-50 transition-colors">
                    <i class="fas fa-university text-2xl text-red-600 mr-4"></i>
                    <div class="text-left">
                        <h5 class="font-medium text-gray-900">BNI</h5>
                        <p class="text-sm text-gray-600">Bank Negara Indonesia</p>
                    </div>
                </button>

                <button onclick="createPaymentForParticipant('virtual_account', 'BRI')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
                    <i class="fas fa-university text-2xl text-green-600 mr-4"></i>
                    <div class="text-left">
                        <h5 class="font-medium text-gray-900">BRI</h5>
                        <p class="text-sm text-gray-600">Bank Rakyat Indonesia</p>
                    </div>
                </button>

                <button onclick="createPaymentForParticipant('virtual_account', 'MANDIRI')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition-colors">
                    <i class="fas fa-university text-2xl text-yellow-600 mr-4"></i>
                    <div class="text-left">
                        <h5 class="font-medium text-gray-900">Mandiri</h5>
                        <p class="text-sm text-gray-600">Bank Mandiri</p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- E-Wallet Selection Modal -->
<div id="dashboardEWalletModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select E-Wallet</h3>
                <button onclick="hideDashboardEWalletModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-3">
                <button onclick="createPaymentForParticipant('ewallet', 'OVO')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
                    <i class="fas fa-mobile-alt text-2xl text-purple-600 mr-4"></i>
                    <div class="text-left">
                        <h5 class="font-medium text-gray-900">OVO</h5>
                        <p class="text-sm text-gray-600">Digital Wallet</p>
                    </div>
                </button>

                <button onclick="createPaymentForParticipant('ewallet', 'DANA')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                    <i class="fas fa-mobile-alt text-2xl text-blue-600 mr-4"></i>
                    <div class="text-left">
                        <h5 class="font-medium text-gray-900">DANA</h5>
                        <p class="text-sm text-gray-600">Digital Wallet</p>
                    </div>
                </button>

                <button onclick="createPaymentForParticipant('ewallet', 'LINKAJA')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
                    <i class="fas fa-mobile-alt text-2xl text-green-600 mr-4"></i>
                    <div class="text-left">
                        <h5 class="font-medium text-gray-900">LinkAja</h5>
                        <p class="text-sm text-gray-600">Digital Wallet</p>
                    </div>
                </button>

                <button onclick="createPaymentForParticipant('ewallet', 'SHOPEEPAY')" class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-colors">
                    <i class="fas fa-mobile-alt text-2xl text-orange-600 mr-4"></i>
                    <div class="text-left">
                        <h5 class="font-medium text-gray-900">ShopeePay</h5>
                        <p class="text-sm text-gray-600">Digital Wallet</p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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

    // Don't load if container doesn't exist
    if (!container) {
        return;
    }

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

// Tab switching function
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-red-600', 'text-red-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Add active class to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.add('active', 'border-red-600', 'text-red-600');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
}

// Payment History Management
let currentParticipantId = null;

// Get CSRF token and API token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const apiBaseUrl = '{{ config('services.backend.base_url') }}';
const apiToken = '{{ Session::get('api_token') }}';

// Show payment modal for a specific participant
function showPaymentModalForParticipant(participantId, eventTitle, amount) {
    currentParticipantId = participantId;

    // Update modal content
    document.getElementById('paymentEventTitle').textContent = eventTitle;
    document.getElementById('paymentEventAmount').textContent = 'Rp ' + formatCurrencyDisplay(amount);

    // Show modal
    document.getElementById('dashboardPaymentModal').classList.remove('hidden');
}

// Hide main payment modal
function hideDashboardPaymentModal() {
    document.getElementById('dashboardPaymentModal').classList.add('hidden');
    currentParticipantId = null;
}

// Show bank selection
function showDashboardBankSelection() {
    hideDashboardPaymentModal();
    document.getElementById('dashboardBankModal').classList.remove('hidden');
}

// Hide bank modal
function hideDashboardBankModal() {
    document.getElementById('dashboardBankModal').classList.add('hidden');
    if (currentParticipantId) {
        showPaymentModalForParticipant(currentParticipantId,
            document.getElementById('paymentEventTitle').textContent,
            parseCurrencyDisplay(document.getElementById('paymentEventAmount').textContent));
    }
}

// Show e-wallet selection
function showDashboardEWalletSelection() {
    hideDashboardPaymentModal();
    document.getElementById('dashboardEWalletModal').classList.remove('hidden');
}

// Hide e-wallet modal
function hideDashboardEWalletModal() {
    document.getElementById('dashboardEWalletModal').classList.add('hidden');
    if (currentParticipantId) {
        showPaymentModalForParticipant(currentParticipantId,
            document.getElementById('paymentEventTitle').textContent,
            parseCurrencyDisplay(document.getElementById('paymentEventAmount').textContent));
    }
}

// Create payment for participant
async function createPaymentForParticipant(paymentMethod, provider = null) {
    if (!currentParticipantId) {
        showToast('Invalid payment request', 'error');
        return;
    }

    // Show loading
    document.getElementById('dashboardPaymentLoading').classList.remove('hidden');

    // Hide all modals
    document.getElementById('dashboardPaymentModal').classList.add('hidden');
    document.getElementById('dashboardBankModal').classList.add('hidden');
    document.getElementById('dashboardEWalletModal').classList.add('hidden');

    // Prepare payment data
    const paymentData = {
        participant_id: currentParticipantId,
        payment_method: paymentMethod
    };

    if (paymentMethod === 'virtual_account' && provider) {
        paymentData.bank_code = provider;
    } else if (paymentMethod === 'ewallet' && provider) {
        paymentData.ewallet_type = provider;
    }

    try {
        const response = await fetch(apiBaseUrl + '/payments/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + apiToken,
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(paymentData)
        });

        const data = await response.json();
        document.getElementById('dashboardPaymentLoading').classList.add('hidden');

        if (data.success && data.data && data.data.payment_url) {
            // Redirect to Xendit payment page
            window.location.href = data.data.payment_url;
        } else if (response.ok) {
            // Payment created but no URL (shouldn't happen for paid events)
            showToast('Payment created successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            // Handle error
            let errorMessage = data.message || 'Failed to create payment';
            if (data.errors) {
                errorMessage += '\n';
                for (const [field, errors] of Object.entries(data.errors)) {
                    errorMessage += '\n• ' + errors.join(', ');
                }
            }
            showToast(errorMessage, 'error');
        }
    } catch (error) {
        document.getElementById('dashboardPaymentLoading').classList.add('hidden');
        console.error('Payment creation error:', error);
        showToast('Network error. Please check your connection and try again.', 'error');
    }
}

// Cancel payment
async function cancelPayment(participantId) {
    if (!confirm('Are you sure you want to cancel this payment? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(apiBaseUrl + '/payments/cancel/' + participantId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + apiToken,
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast('Payment cancelled successfully', 'success');
            // Reload page to refresh payment history
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to cancel payment', 'error');
        }
    } catch (error) {
        console.error('Cancel payment error:', error);
        showToast('Network error. Please try again.', 'error');
    }
}

// Helper: Format currency for display
function formatCurrencyDisplay(amount) {
    return new Intl.NumberFormat('id-ID').format(amount);
}

// Helper: Parse currency from formatted string
function parseCurrencyDisplay(formattedAmount) {
    return parseInt(formattedAmount.replace(/[^0-9]/g, ''));
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.id === 'dashboardPaymentModal') {
        hideDashboardPaymentModal();
    }
    if (event.target.id === 'dashboardBankModal') {
        hideDashboardBankModal();
    }
    if (event.target.id === 'dashboardEWalletModal') {
        hideDashboardEWalletModal();
    }
});
</script>
@endpush

@endsection