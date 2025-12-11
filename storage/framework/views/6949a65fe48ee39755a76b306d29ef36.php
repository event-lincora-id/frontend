<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Not Found - Event Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <!-- 404 Icon -->
            <div class="mx-auto h-24 w-24 flex items-center justify-center rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
            </div>
            
            <!-- Error Message -->
            <div>
                <h1 class="text-4xl font-bold text-gray-900 mb-4">404</h1>
                <h2 class="text-2xl font-semibold text-gray-700 mb-2">Event Not Found</h2>
                <p class="text-gray-600 mb-6">
                    Sorry, the event you're looking for doesn't exist or may have been removed.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <a href="<?php echo e(route('events.index')); ?>" 
                   class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200 inline-block">
                    <i class="fas fa-list mr-2"></i>Browse All Events
                </a>
                
                <a href="<?php echo e(url('/')); ?>" 
                   class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors duration-200 inline-block">
                    <i class="fas fa-home mr-2"></i>Go Home
                </a>
            </div>

            <!-- Help Text -->
            <div class="mt-8 text-sm text-gray-500">
                <p>If you believe this is an error, please contact support.</p>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\event-connect\resources\views/errors/404.blade.php ENDPATH**/ ?>