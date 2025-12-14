<?php $__env->startSection('title', 'Scan QR Code - Event Connect'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Scan QR Code</h1>
            <p class="text-gray-600">Scan the QR code provided by the event organizer to mark your attendance</p>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo e(session('success')); ?></span>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo e(session('error')); ?></span>
            </div>
        <?php endif; ?>

        <div class="mb-6">
            <div id="scanner-container" class="relative bg-gray-100 rounded-lg overflow-hidden" style="min-height: 400px;">
                <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>
                <canvas id="canvas" class="hidden"></canvas>
                <div id="scanner-overlay" class="absolute inset-0 flex items-center justify-center">
                    <div class="border-4 border-blue-500 rounded-lg w-64 h-64 relative">
                        <div class="absolute -top-1 -left-1 w-8 h-8 border-t-4 border-l-4 border-blue-500 rounded-tl-lg"></div>
                        <div class="absolute -top-1 -right-1 w-8 h-8 border-t-4 border-r-4 border-blue-500 rounded-tr-lg"></div>
                        <div class="absolute -bottom-1 -left-1 w-8 h-8 border-b-4 border-l-4 border-blue-500 rounded-bl-lg"></div>
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 border-b-4 border-r-4 border-blue-500 rounded-br-lg"></div>
                    </div>
                </div>
            </div>
            <div id="status-message" class="mt-4 text-center text-gray-600"></div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4">
            <button id="start-scanner" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                <i class="fas fa-camera mr-2"></i> Start Scanner
            </button>
            <button id="stop-scanner" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 hidden">
                <i class="fas fa-stop mr-2"></i> Stop Scanner
            </button>
            <button id="manual-input" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                <i class="fas fa-keyboard mr-2"></i> Manual Input
            </button>
        </div>

        <!-- Manual Input Modal -->
        <div id="manual-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Enter QR Code Manually</h3>
                    <form id="manual-form" method="POST" action="<?php echo e(route('attendance.mark')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="mb-4">
                            <label for="qr_code" class="block text-sm font-medium text-gray-700 mb-2">QR Code</label>
                            <input type="text" id="qr_code" name="qr_code" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Submit
                            </button>
                            <button type="button" id="close-modal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-3">
            <i class="fas fa-info-circle mr-2"></i> How to Use
        </h3>
        <ul class="list-disc list-inside text-blue-800 space-y-2">
            <li>Click "Start Scanner" to activate your camera</li>
            <li>Allow camera permissions when prompted</li>
            <li>Point your camera at the QR code provided by the event organizer</li>
            <li>Your attendance will be automatically marked when QR code is detected</li>
            <li>If scanning doesn't work, use "Manual Input" to enter the QR code manually</li>
        </ul>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
    let scannerActive = false;
    let stream = null;
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    const statusMessage = document.getElementById('status-message');
    const startButton = document.getElementById('start-scanner');
    const stopButton = document.getElementById('stop-scanner');
    const manualInputButton = document.getElementById('manual-input');
    const manualModal = document.getElementById('manual-modal');
    const closeModal = document.getElementById('close-modal');

    startButton.addEventListener('click', startScanner);
    stopButton.addEventListener('click', stopScanner);
    manualInputButton.addEventListener('click', () => {
        manualModal.classList.remove('hidden');
    });
    closeModal.addEventListener('click', () => {
        manualModal.classList.add('hidden');
    });

    async function startScanner() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'environment' // Use back camera on mobile
                } 
            });
            
            video.srcObject = stream;
            scannerActive = true;
            startButton.classList.add('hidden');
            stopButton.classList.remove('hidden');
            statusMessage.textContent = 'Scanning... Point camera at QR code';
            statusMessage.className = 'mt-4 text-center text-blue-600';
            
            scanQR();
        } catch (error) {
            console.error('Error accessing camera:', error);
            statusMessage.textContent = 'Error: Could not access camera. Please check permissions.';
            statusMessage.className = 'mt-4 text-center text-red-600';
        }
    }

    function stopScanner() {
        scannerActive = false;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        video.srcObject = null;
        startButton.classList.remove('hidden');
        stopButton.classList.add('hidden');
        statusMessage.textContent = 'Scanner stopped';
        statusMessage.className = 'mt-4 text-center text-gray-600';
    }

    function scanQR() {
        if (!scannerActive) return;

        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            
            if (code) {
                // QR code detected
                statusMessage.textContent = 'QR Code detected! Processing...';
                statusMessage.className = 'mt-4 text-center text-green-600';
                
                // Submit attendance
                submitAttendance(code.data);
            } else {
                // Continue scanning
                requestAnimationFrame(scanQR);
            }
        } else {
            requestAnimationFrame(scanQR);
        }
    }

    function submitAttendance(qrCode) {
        // Stop scanner
        stopScanner();
        
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo e(route("attendance.mark")); ?>';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '<?php echo e(csrf_token()); ?>';
        form.appendChild(csrfInput);
        
        const qrInput = document.createElement('input');
        qrInput.type = 'hidden';
        qrInput.name = 'qr_code';
        qrInput.value = qrCode;
        form.appendChild(qrInput);
        
        document.body.appendChild(form);
        form.submit();
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        stopScanner();
    });
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('participant.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Study\Kuliah\Semester-7\CP\event-connect\resources\views/participant/attendance/scanner.blade.php ENDPATH**/ ?>