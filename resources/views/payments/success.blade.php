<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Event Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <!-- Success Icon -->
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
                    <i class="fas fa-check text-4xl text-green-600"></i>
                </div>

                <!-- Success Message -->
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
                <p class="text-lg text-gray-600 mb-8">Your payment has been processed successfully.</p>

                @if($participant && $event)
                    <!-- Event Details -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Event Details</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Event:</span>
                                <span class="font-medium text-gray-900">{{ $event->title }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y H:i') }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Location:</span>
                                <span class="font-medium text-gray-900">{{ $event->location }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Amount Paid:</span>
                                <span class="font-medium text-green-600">Rp {{ number_format($participant->amount_paid ?? $event->price) }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Paid
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Reference -->
                    @if($participant->payment_reference)
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <p class="text-sm text-gray-600 mb-2">Payment Reference:</p>
                            <p class="font-mono text-sm text-gray-900">{{ $participant->payment_reference }}</p>
                        </div>
                    @endif
                @endif

                <!-- Action Buttons -->
                <div class="space-y-4">
                    <a href="{{ route('events.index') }}" 
                       class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-search mr-2"></i>Browse More Events
                    </a>
                    
                    @auth
                        <a href="{{ route('participant.dashboard') }}" 
                           class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-tachometer-alt mr-2"></i>Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login to Dashboard
                        </a>
                    @endauth
                </div>

                <!-- Additional Info -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-500">
                        You will receive a confirmation email shortly with your event details and QR code.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Verify payment status on page load -->
    <script>
        // CRITICAL: Call backend to verify payment and update participant status
        @if($participant)
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Verifying payment status for participant {{ $participant->id }}...');

            fetch('{{ env("API_BASE_URL") }}/payments/status/{{ $participant->id }}', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer {{ Session::get("api_token") }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Payment verification response:', data);

                if (data.success && data.data.is_paid) {
                    console.log('✅ Payment verified and participant status updated');
                } else {
                    console.warn('⚠️ Payment verification returned unexpected status:', data);
                }
            })
            .catch(error => {
                console.error('Failed to verify payment status:', error);
            });
        });
        @endif

        // Auto redirect after 10 seconds
        setTimeout(function() {
            @if(auth()->check())
                window.location.href = "{{ route('participant.dashboard') }}";
            @else
                window.location.href = "{{ route('events.index') }}";
            @endif
        }, 10000);
    </script>
</body>
</html>




