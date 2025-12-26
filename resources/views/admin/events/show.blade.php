@extends('admin.layout')

@section('title', 'Event Details')
@section('page-title', 'Event Details')
@section('page-description', 'View event information and participants')

@section('content')
<div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <h3 class="text-lg font-medium text-gray-900">{{ $event->title ?? 'Event Details' }}</h3>
            <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                <a href="{{ route('admin.events.edit', $event->id) }}" style="background-color: var(--color-primary);" class="text-white px-4 py-2 rounded-md hover:opacity-90 text-center">
                    <i class="fas fa-edit mr-2"></i>Edit Event
                </a>
                <a href="{{ route('admin.events.feedbacks', $event->id) }}" style="background-color: #9333ea !important; color: white !important; display: inline-block !important;" class="px-4 py-2 rounded-md hover:bg-purple-700 text-center">
                    <i class="fas fa-comments mr-2"></i>View Feedbacks
                </a>
                <a href="{{ route('attendance.qr-code', $event->id) }}" style="background-color: #3b82f6 !important; color: white !important; display: inline-block !important;" class="px-4 py-2 rounded-md hover:bg-blue-700 text-center">
                    <i class="fas fa-qrcode mr-2"></i>View QR Code
                </a>
                <a href="{{ route('admin.events.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Events
                </a>
            </div>
        </div>
    </div>

    <!-- Event Details -->
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Event Information -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Event Information</h4>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $event->title }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $event->description }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $event->category->name ?? 'Uncategorized' }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $event->location }}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Max Participants</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $event->max_participants ?? 'Unlimited' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price</label>
                            <p class="mt-1 text-sm text-gray-900">Rp {{ number_format($event->price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        @php
                            $now = now();
                            $startDate = \Carbon\Carbon::parse($event->start_date);
                            $endDate = \Carbon\Carbon::parse($event->end_date);
                            
                            if ($now < $startDate) {
                                $status = 'upcoming';
                                $statusClass = 'bg-blue-100 text-blue-800';
                            } elseif ($now >= $startDate && $now <= $endDate) {
                                $status = 'ongoing';
                                $statusClass = 'bg-green-100 text-green-800';
                            } else {
                                $status = 'completed';
                                $statusClass = 'bg-gray-100 text-gray-800';
                            }
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Event Image -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Event Image</h4>
                @if($event->image)
                    <img class="w-full h-64 object-cover rounded-lg" src="{{ $event->image_url ?? asset('storage/' . $event->image) }}" alt="{{ $event->title }}">
                @else
                    <div class="w-full h-64 bg-gray-300 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar text-gray-600 text-4xl"></i>
                    </div>
                @endif
            </div>
        </div>

        <!-- Participant Summary Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6 mt-8">
            <h4 class="text-lg font-semibold mb-4">Participant Summary</h4>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <!-- Total Registered -->
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-users text-3xl text-gray-600 mb-2"></i>
                    <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-600">Total Registered</div>
                </div>

                <!-- Attended -->
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <i class="fas fa-check-circle text-3xl text-green-600 mb-2"></i>
                    <div class="text-2xl font-bold text-green-600">{{ $stats['attended'] }}</div>
                    <div class="text-sm text-gray-600">Attended</div>
                </div>

                <!-- Pending -->
                <div class="text-center p-4 bg-yellow-50 rounded-lg">
                    <i class="fas fa-clock text-3xl text-yellow-600 mb-2"></i>
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                    <div class="text-sm text-gray-600">Pending</div>
                </div>

                <!-- Cancelled -->
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <i class="fas fa-times-circle text-3xl text-red-600 mb-2"></i>
                    <div class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</div>
                    <div class="text-sm text-gray-600">Cancelled</div>
                </div>
            </div>

            <!-- Attendance Rate Progress Bar -->
            <div class="mt-4">
                <div class="flex justify-between text-sm mb-1">
                    <span>Attendance Rate</span>
                    <span class="font-semibold">{{ $attendanceRate }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $attendanceRate }}%"></div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h4 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-dollar-sign mr-2 text-green-600"></i>
                Event Revenue
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Total Revenue -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Total Revenue</div>
                    <div class="text-2xl font-bold text-green-600">
                        Rp {{ number_format($revenue['total'], 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $revenue['paid_count'] }} paid participants
                    </div>
                </div>

                <!-- Organizer Earnings -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg">
                    <div class="text-sm text-gray-600 mb-1">Your Earnings (95%)</div>
                    <div class="text-2xl font-bold text-blue-600">
                        Rp {{ number_format($revenue['organizer_earnings'], 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        Platform fee: Rp {{ number_format($revenue['platform_fee'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Participants Section -->
        <div class="mt-8">
            @php
                $participantsCollection = collect($participants ?? []);
            @endphp

            <div class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-medium text-gray-900">Participants List ({{ $participantsCollection->count() }})</h4>
                @if($participantsCollection->count() > 0)
                    <button onclick="exportParticipants()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </button>
                @endif
            </div>
            
            @if($participantsCollection->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attended At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($participantsCollection as $index => $participant)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center text-white font-medium text-xs">
                                                {{ strtoupper(substr($participant->user->full_name ?? $participant->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $participant->user->full_name ?? $participant->user->name ?? 'Unknown' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $participant->user->email ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if(($participant->status ?? '') == 'registered') bg-yellow-100 text-yellow-800
                                        @elseif(($participant->status ?? '') == 'attended') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($participant->status ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(($participant->is_paid ?? false))
                                        <span class="text-green-600 font-semibold">
                                            Paid - Rp {{ number_format($participant->amount_paid ?? 0, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">Free</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ isset($participant->registered_at) ? \Carbon\Carbon::parse($participant->registered_at)->format('M d, Y h:i A') : (isset($participant->created_at) ? \Carbon\Carbon::parse($participant->created_at)->format('M d, Y h:i A') : '-') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ isset($participant->attended_at) ? \Carbon\Carbon::parse($participant->attended_at)->format('M d, Y h:i A') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-users text-4xl mb-4"></i>
                    <p>No participants yet</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportParticipants() {
    // Get participants data
    const participants = @json($participants);
    const eventTitle = "{{ $event->title }}";

    // Create CSV content
    let csv = 'No,Name,Email,Status,Payment Status,Amount,Registered At,Attended At\n';

    participants.forEach((participant, index) => {
        const name = participant.user?.full_name || participant.user?.name || 'Unknown';
        const email = participant.user?.email || '-';
        const status = participant.status || 'pending';

        const paymentStatus = participant.is_paid ? 'Paid' : 'Free';
        const amount = participant.is_paid
            ? 'Rp ' + (participant.amount_paid || 0).toLocaleString('id-ID')
            : '-';

        const registeredAt = participant.registered_at || participant.created_at || '-';
        const attendedAt = participant.attended_at || '-';

        csv += `${index + 1},`;
        csv += `"${name}",`;
        csv += `"${email}",`;
        csv += `"${status}",`;
        csv += `"${paymentStatus}",`;
        csv += `"${amount}",`;
        csv += `"${registeredAt}",`;
        csv += `"${attendedAt}"\n`;
    });

    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', eventTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '_participants.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
}
</script>
@endsection
