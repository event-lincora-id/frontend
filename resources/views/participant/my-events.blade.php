@extends('participant.layout')

@section('title', 'My Participations')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">My Participations</h1>
        <p class="text-gray-600 mt-2">Events you have joined and your QR codes</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('notification'))
        <div class="mb-6 bg-blue-100 border border-blue-400 text-blue-700 px-6 py-4 rounded-lg shadow-lg">
            <div class="flex items-start">
                <i class="fas fa-bell text-2xl mr-4 mt-1"></i>
                <div>
                    <h3 class="font-bold text-lg">{{ session('notification')['title'] }}</h3>
                    <p>{{ session('notification')['message'] }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(isset($error))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ $error }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($participations as $participation)
            @php
                $event = $participation->event ?? null;
            @endphp
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                <!-- Event Image -->
                <div class="h-48 bg-gradient-to-r from-red-500 to-pink-500 relative">
                    @if(isset($event->image_url) && $event->image_url)
                        <img src="{{ $event->image_url }}" alt="{{ $event->title ?? 'Event' }}" class="w-full h-full object-cover">
                    @else
                        <div class="flex items-center justify-center h-full">
                            <i class="fas fa-calendar-alt text-6xl text-white opacity-50"></i>
                        </div>
                    @endif
                    
                    <!-- Status Badge -->
                    @php
                        $statusConfig = [
                            'registered' => ['class' => 'bg-blue-500', 'text' => 'Registered'],
                            'attended' => ['class' => 'bg-green-500', 'text' => 'Attended'],
                            'cancelled' => ['class' => 'bg-red-500', 'text' => 'Cancelled'],
                        ];
                        $status = $statusConfig[$participation->status ?? 'registered'] ?? $statusConfig['registered'];
                    @endphp
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 rounded-full text-white text-sm font-semibold {{ $status['class'] }}">
                            {{ $status['text'] }}
                        </span>
                    </div>
                </div>

                <!-- Event Info -->
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $event->title ?? 'Event Title' }}</h3>
                    
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt w-5 text-red-500"></i>
                            <span class="ml-2">
                                @if(isset($event->start_date))
                                    {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y - H:i') }}
                                @else
                                    TBA
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt w-5 text-red-500"></i>
                            <span class="ml-2">{{ $event->location ?? 'Location TBA' }}</span>
                        </div>
                        @if(isset($event->category))
                            <div class="flex items-center">
                                <i class="fas fa-tag w-5 text-red-500"></i>
                                <span class="ml-2">{{ is_object($event->category) ? $event->category->name : $event->category['name'] ?? 'Uncategorized' }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Registration Info -->
                    <div class="border-t pt-4 mb-4">
                        <p class="text-xs text-gray-500">Registered on</p>
                        <p class="text-sm font-semibold text-gray-700">
                            {{ \Carbon\Carbon::parse($participation->created_at ?? now())->format('M d, Y H:i') }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <a href="{{ route('participations.qr-code', $participation->id) }}" 
                           class="flex-1 bg-red-600 hover:bg-red-700 text-white text-center py-2 px-4 rounded-lg font-semibold transition-colors">
                            <i class="fas fa-qrcode mr-2"></i>Show QR Code
                        </a>
                        <a href="{{ route('events.show', $event->id ?? 1) }}" 
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg transition-colors">
                            <i class="fas fa-info-circle"></i>
                        </a>
                    </div>

                    @if($participation->status !== 'attended' && $participation->status !== 'cancelled')
                        <form action="{{ route('participations.cancel', $participation->id) }}" method="POST" class="mt-2" onsubmit="return confirm('Are you sure you want to cancel this participation?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-red-600 hover:text-red-800 text-sm py-2">
                                <i class="fas fa-times-circle mr-1"></i>Cancel Participation
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Participations Yet</h3>
                <p class="text-gray-500 mb-6">You haven't joined any events. Start exploring and join events!</p>
                <a href="{{ route('events.explore') }}" class="inline-block bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold">
                    <i class="fas fa-search mr-2"></i>Explore Events
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
