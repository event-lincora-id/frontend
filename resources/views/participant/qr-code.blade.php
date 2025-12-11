@extends('layout')

@section('title', 'Event QR Code')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('my.participations') }}" class="text-red-600 hover:text-red-700 font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Back to My Participations
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-500 to-pink-500 text-white p-6">
                <h1 class="text-2xl font-bold mb-2">Your Event QR Code</h1>
                <p class="text-white/90">Show this QR code for attendance verification</p>
            </div>

            <!-- Event Info -->
            <div class="p-6 border-b">
                @php
                    $event = $participation->event ?? null;
                @endphp
                <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $event->title ?? 'Event' }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Date & Time</p>
                        <p class="font-semibold text-gray-900">
                            @if(isset($event->start_date))
                                {{ \Carbon\Carbon::parse($event->start_date)->format('l, M d, Y') }}<br>
                                {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($event->end_date ?? $event->start_date)->format('H:i') }}
                            @else
                                TBA
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500">Location</p>
                        <p class="font-semibold text-gray-900">{{ $event->location ?? 'TBA' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Participant Name</p>
                        <p class="font-semibold text-gray-900">{{ $participation->user->full_name ?? $participation->user->name ?? 'Participant' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Registration Date</p>
                        <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($participation->created_at ?? now())->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            <div class="p-8 bg-gray-50">
                <div class="bg-white p-8 rounded-lg shadow-inner max-w-sm mx-auto">
                    <div class="flex justify-center mb-4">
                        <div id="qrcode" class="qr-code-container"></div>
                    </div>
                    
                    <!-- QR Code Data -->
                    <div class="text-center">
                        <p class="text-xs text-gray-500 mb-2">Participation Code</p>
                        <p class="font-mono text-sm font-bold text-gray-700 bg-gray-100 px-4 py-2 rounded">
                            {{ $participation->qr_code ?? 'P-' . str_pad($participation->id ?? '000', 6, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>How to Use
                    </h3>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li>Show this QR code to the event organizer upon arrival</li>
                        <li>The organizer will scan your QR code to mark your attendance</li>
                        <li>Keep this page accessible on your device during the event</li>
                        <li>Screenshot this QR code for offline access</li>
                    </ul>
                </div>

                <!-- Download Button -->
                <div class="mt-4 flex gap-3">
                    <button onclick="downloadQR()" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-download mr-2"></i>Download QR Code
                    </button>
                    <button onclick="window.print()" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-3 px-6 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                </div>
            </div>

            <!-- Status -->
            <div class="p-6 bg-gray-100">
                @php
                    $statusConfig = [
                        'registered' => ['class' => 'bg-blue-100 text-blue-800 border-blue-300', 'icon' => 'fa-clock', 'text' => 'Waiting for Event'],
                        'attended' => ['class' => 'bg-green-100 text-green-800 border-green-300', 'icon' => 'fa-check-circle', 'text' => 'Attended'],
                        'cancelled' => ['class' => 'bg-red-100 text-red-800 border-red-300', 'icon' => 'fa-times-circle', 'text' => 'Cancelled'],
                    ];
                    $status = $statusConfig[$participation->status ?? 'registered'] ?? $statusConfig['registered'];
                @endphp
                <div class="text-center">
                    <div class="inline-flex items-center px-4 py-2 rounded-full border-2 {{ $status['class'] }} font-semibold">
                        <i class="fas {{ $status['icon'] }} mr-2"></i>
                        <span>{{ $status['text'] }}</span>
                    </div>
                    @if($participation->attended_at ?? false)
                        <p class="text-sm text-gray-600 mt-2">
                            Attended on {{ \Carbon\Carbon::parse($participation->attended_at)->format('M d, Y - H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden canvas for QR download -->
<canvas id="qr-canvas" style="display:none;"></canvas>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
// QR Code data from server
const qrData = @json($qrData);

// Generate QR Code
const qrcode = new QRCode(document.getElementById("qrcode"), {
    text: qrData,
    width: 256,
    height: 256,
    colorDark: "#000000",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
});

// Download QR Code
function downloadQR() {
    const canvas = document.querySelector('#qrcode canvas');
    if (canvas) {
        const url = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = 'event-qr-code-{{ $participation->id }}.png';
        link.href = url;
        link.click();
    }
}

// Print styles
const style = document.createElement('style');
style.innerHTML = `
    @media print {
        body * { visibility: hidden; }
        .container { max-width: none !important; }
        .bg-white, #qrcode, #qrcode * { visibility: visible; }
        .bg-white { 
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }
    }
`;
document.head.appendChild(style);
</script>

<style>
.qr-code-container {
    display: inline-block;
}
#qrcode {
    padding: 16px;
    background: white;
    border-radius: 8px;
}
#qrcode img {
    margin: 0 auto;
    display: block;
}
</style>
@endsection
