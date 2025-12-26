<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $event->title }} - Event Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- QR Code Generator Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Mobile Menu Button -->
    <button id="mobile-menu-btn" class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg text-white shadow-lg" style="background-color: #B22234;">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Overlay for mobile menu -->
    <div id="mobile-overlay" class="hidden lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30"></div>

    <!-- Sidebar -->
    <div class="flex h-screen">
        <div id="sidebar" class="fixed lg:static w-64 h-full shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-40" style="background-color: #B22234;">
            <div class="p-4 lg:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-8 lg:h-10 w-8 lg:w-10 rounded-full bg-white/20 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-sm lg:text-base"></i>
                        </div>
                        <div>
                            <h1 class="text-lg lg:text-xl font-bold text-white">Event Connect</h1>
                            <p class="text-white/80 text-xs">Admin Dashboard</p>
                        </div>
                    </div>
                    <button id="close-sidebar-btn" class="lg:hidden text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <nav class="mt-4 lg:mt-6">
                <a href="/admin/dashboard" class="flex items-center px-4 lg:px-6 py-3 text-white/80 hover:bg-white/10">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="/admin/events" class="flex items-center px-4 lg:px-6 py-3 text-white bg-white/20 border-r-4 border-white">
                    <i class="fas fa-calendar-alt mr-3"></i>
                    Events
                </a>
                <a href="/admin/categories" class="flex items-center px-4 lg:px-6 py-3 text-white/80 hover:bg-white/10">
                    <i class="fas fa-tags mr-3"></i>
                    Categories
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto w-full lg:w-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-4 lg:px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 lg:ml-0 ml-12">
                    <div class="flex-1">
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 break-words">QR Code - {{ $event->title }}</h2>
                        <p class="text-sm sm:text-base text-gray-600 mt-1">Use this QR code for event attendance</p>
                    </div>
                    <a href="{{ route('admin.events.index') }}" class="bg-gray-600 text-white px-4 lg:px-6 py-2 lg:py-3 rounded-lg hover:bg-gray-700 font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center text-sm lg:text-base whitespace-nowrap">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Events
                    </a>
                </div>
            </header>

            <!-- Content -->
            <div class="p-3 sm:p-4 lg:p-6">
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 lg:p-8">
                    <div class="text-center mb-4 lg:mb-6">
                        <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-2">{{ $event->title }}</h3>
                        <p class="text-sm sm:text-base text-gray-600">{{ $event->location }}</p>
                        <p class="text-gray-500 text-xs sm:text-sm">{{ \Carbon\Carbon::parse($event->start_date)->format('l, F d, Y') }}</p>
                    </div>

                    @if($event->qr_code_string)
                        <div class="flex flex-col lg:flex-row gap-4 sm:gap-6 lg:gap-8 items-center justify-center">
                            <!-- QR Code Display -->
                            <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg border-4 w-full max-w-xs" style="border-color: #B22234;">
                                <div id="qrcode-container" class="flex justify-center"></div>
                                <p class="text-center text-gray-600 mt-3 sm:mt-4 text-xs sm:text-sm">Scan this QR code for attendance</p>
                            </div>

                            <!-- QR Code Info -->
                            <div class="space-y-3 sm:space-y-4 w-full lg:w-auto">
                                <div class="rounded-lg p-4" style="background-color: rgba(178, 34, 52, 0.1); border: 1px solid rgba(178, 34, 52, 0.2);">
                                    <h4 class="font-semibold mb-2" style="color: #8F1C2A;">
                                        <i class="fas fa-info-circle mr-2"></i>Instructions
                                    </h4>
                                    <ul class="list-disc list-inside text-sm space-y-1" style="color: #B22234;">
                                        <li>Display this QR code to participants at the event</li>
                                        <li>Participants will scan using their mobile devices</li>
                                        <li>Attendance will be automatically marked upon successful scan</li>
                                        <li>You can view attendance status in the Participants page</li>
                                    </ul>
                                </div>

                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-green-900 mb-2">
                                        <i class="fas fa-users mr-2"></i>Attendance Status
                                    </h4>
                                        @php
                                            $participantsCollection = collect($participants ?? []);
                                            $registeredCount = isset($event->registered_count) ? $event->registered_count : ($participantsCollection->count());
                                            $attendedCount = $participantsCollection->where('status', 'attended')->count();
                                        @endphp
                                        <div class="text-green-800 text-sm">
                                            <p><strong>Registered:</strong> {{ $registeredCount }} participants</p>
                                            <p><strong>Attended:</strong> {{ $attendedCount }} participants</p>
                                        </div>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                    <a href="{{ route('attendance.participants', $event->id) }}"
                                       style="background-color: #B22234;" class="flex-1 hover:opacity-90 text-white font-bold py-2 px-3 sm:px-4 rounded-lg text-center transition duration-200 text-sm sm:text-base">
                                        <i class="fas fa-list mr-2"></i>View Participants
                                    </a>
                                    <button onclick="downloadQR()"
                                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 sm:px-4 rounded-lg transition duration-200 text-sm sm:text-base">
                                        <i class="fas fa-download mr-2"></i>Download QR
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code String (for manual entry) -->
                        <div class="mt-4 sm:mt-6 bg-gray-50 border border-gray-200 rounded-lg p-3 sm:p-4">
                            <h4 class="font-semibold text-gray-900 mb-2 text-sm sm:text-base">
                                <i class="fas fa-keyboard mr-2"></i>QR Code String (for manual entry)
                            </h4>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <input type="text"
                                       id="qr-code-string"
                                       value="{{ $event->qr_code_string }}"
                                       readonly
                                       class="flex-1 px-2 sm:px-3 py-2 border border-gray-300 rounded-md bg-white font-mono text-xs sm:text-sm">
                                <button onclick="copyQRCode()"
                                        class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded transition duration-200 text-sm sm:text-base whitespace-nowrap">
                                    <i class="fas fa-copy mr-2"></i>Copy
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-qrcode text-6xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">QR Code Not Generated</h3>
                            <p class="text-gray-600 mb-4">QR code will be generated automatically when you publish the event.</p>
                            <a href="{{ route('admin.events.edit', $event->id) }}" 
                               style="background-color: #B22234;" class="inline-block hover:opacity-90 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                                Edit Event
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const closeSidebarBtn = document.getElementById('close-sidebar-btn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }

            mobileMenuBtn.addEventListener('click', openSidebar);
            closeSidebarBtn.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);

            // Generate QR code on page load
            @if(isset($event->qr_code_string))
            const qrCodeString = '{{ $event->qr_code_string }}';
            const container = document.getElementById('qrcode-container');

            if (container && qrCodeString) {
                // Responsive QR code size
                const isMobile = window.innerWidth < 640;
                const qrSize = isMobile ? 200 : 256;

                // Generate QR code using qrcode.js
                new QRCode(container, {
                    text: qrCodeString,
                    width: qrSize,
                    height: qrSize,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
            @endif
        });

        function downloadQR() {
            // Get the generated QR code canvas
            const qrCanvas = document.querySelector('#qrcode-container canvas');

            if (qrCanvas) {
                // Convert canvas to blob and download
                qrCanvas.toBlob(function(blob) {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'qr-code-{{ $event->id }}.png';
                    link.click();
                    URL.revokeObjectURL(link.href);
                });
            } else {
                alert('QR code not yet generated. Please wait a moment and try again.');
            }
        }

        function copyQRCode() {
            const input = document.getElementById('qr-code-string');
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices

            navigator.clipboard.writeText(input.value).then(() => {
                alert('QR code string copied to clipboard!');
            }).catch(() => {
                // Fallback for older browsers
                document.execCommand('copy');
                alert('QR code string copied to clipboard!');
            });
        }
    </script>
</body>
</html>

