<?php $__env->startSection('title', 'Analytics Dashboard'); ?>
<?php $__env->startSection('page-title', 'Analytics Dashboard'); ?>
<?php $__env->startSection('page-description', 'Comprehensive analytics and insights for your events'); ?>

<?php $__env->startSection('content'); ?>
<!-- Analytics Overview Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Events</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo e($monthlyData['total_events'] ?? 0); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                    <i class="fas fa-users text-white"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Participants</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo e($monthlyData['total_participants'] ?? 0); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                    <i class="fas fa-calendar-check text-white"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Active Events</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo e($monthlyData['active_events'] ?? 0); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                    <i class="fas fa-percentage text-white"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Avg. Attendance</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo e($monthlyData['avg_attendance'] ?? 0); ?>%</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Events Over Time Chart -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Events Over Time</h3>
        <canvas id="eventsChart" width="400" height="200"></canvas>
    </div>
    
    <!-- Category Distribution Chart -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Events by Category</h3>
        <canvas id="categoryChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- User Growth Chart -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h3 class="text-lg font-medium text-gray-900 mb-4">User Growth</h3>
    <canvas id="userGrowthChart" width="800" height="300"></canvas>
</div>

<!-- Detailed Statistics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Top Events -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Top Events by Participants</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $monthlyData['top_events'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-calendar text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?php echo e($event['title'] ?? 'Unknown Event'); ?></p>
                            <p class="text-sm text-gray-500"><?php echo e($event['category'] ?? 'Uncategorized'); ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900"><?php echo e($event['participants'] ?? 0); ?></p>
                        <p class="text-xs text-gray-500">participants</p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-gray-500 text-center py-4">No events data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Category Statistics -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Category Statistics</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $categoryStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full mr-3" style="background-color: <?php echo e($category['color'] ?? '#3B82F6'); ?>"></div>
                        <span class="text-sm font-medium text-gray-900"><?php echo e($category['name'] ?? 'Unknown'); ?></span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo e($category['percentage'] ?? 0); ?>%"></div>
                        </div>
                        <span class="text-sm text-gray-500"><?php echo e($category['count'] ?? 0); ?></span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-gray-500 text-center py-4">No category data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Export and Actions -->
<div class="mt-8 flex justify-between items-center">
    <div class="flex space-x-4">
        <a href="<?php echo e(route('admin.analytics.export')); ?>" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
            <i class="fas fa-download mr-2"></i>Export Data
        </a>
        <a href="<?php echo e(route('admin.analytics.realtime')); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
            <i class="fas fa-sync mr-2"></i>Real-time View
        </a>
    </div>
    
    <div class="text-sm text-gray-500">
        Last updated: <?php echo e(now()->format('M d, Y H:i')); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
// Events Over Time Chart
const eventsCtx = document.getElementById('eventsChart').getContext('2d');
new Chart(eventsCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($monthlyData['months'] ?? [], 15, 512) ?>,
        datasets: [{
            label: 'Events Created',
            data: <?php echo json_encode($monthlyData['events_data'] ?? [], 15, 512) ?>,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Category Distribution Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(collect($categoryStats)->pluck('name')->toArray(), 15, 512) ?>,
        datasets: [{
            data: <?php echo json_encode(collect($categoryStats)->pluck('count')->toArray(), 15, 512) ?>,
            backgroundColor: <?php echo json_encode(collect($categoryStats)->pluck('color')->toArray(), 15, 512) ?>
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// User Growth Chart
const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
new Chart(userGrowthCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($userTrends['months'] ?? [], 15, 512) ?>,
        datasets: [{
            label: 'New Users',
            data: <?php echo json_encode($userTrends['users_data'] ?? [], 15, 512) ?>,
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
            borderColor: 'rgb(34, 197, 94)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Study\Kuliah\Semester-7\CP\event-connect\resources\views/admin/analytics/index.blade.php ENDPATH**/ ?>