

<?php $__env->startSection('title', 'Event Organizers Management'); ?>
<?php $__env->startSection('page-title', 'Event Organizers'); ?>
<?php $__env->startSection('page-description', 'Manage all event organizers on the Event Connect platform'); ?>

<?php $__env->startSection('content'); ?>

<!-- Error Message -->
<?php if(isset($error)): ?>
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
        <p class="text-red-800 font-semibold"><?php echo e($error); ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Search and Filter Section -->
<div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6 md:mb-8">
    <form method="GET" action="<?php echo e(route('super.admin.organizers')); ?>" class="flex flex-col md:flex-row md:items-end gap-4">
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Organizers</label>
            <div class="relative">
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    placeholder="Search by name or email..."
                    value="<?php echo e(request('search')); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" id="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                <option value="">All Status</option>
                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
                <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                <option value="suspended" <?php echo e(request('status') === 'suspended' ? 'selected' : ''); ?>>Suspended</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                <i class="fas fa-search"></i>
                Search
            </button>
            <?php if(request('search') || request('status')): ?>
            <a href="<?php echo e(route('super.admin.organizers')); ?>" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                Clear
            </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if(request('search') || request('status')): ?>
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
        <i class="fas fa-info-circle mr-2"></i>
        Showing organizers <?php echo e(request('search') ? "matching '" . request('search') . "'" : ''); ?><?php echo e(request('status') && request('search') ? ' and ' : ''); ?><?php echo e(request('status') ? "with status '" . request('status') . "'" : ''); ?>

    </div>
    <?php endif; ?>
</div>

<!-- Results Counter -->
<div class="mb-4 text-sm text-gray-600">
    <?php if($organizers->count() > 0): ?>
        <span class="font-semibold text-gray-800">Showing <?php echo e($organizers->firstItem()); ?> to <?php echo e($organizers->lastItem()); ?> of <?php echo e($organizers->total()); ?> organizers</span>
    <?php else: ?>
        <?php if(request('search') || request('status')): ?>
            <span class="text-gray-500">No organizers found matching your criteria</span>
        <?php else: ?>
            <span class="text-gray-500">No organizers yet</span>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Organizers Grid -->
<?php if($organizers->count() > 0): ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
    <?php $__currentLoopData = $organizers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organizer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-blue-500 p-4 md:p-6">
        <!-- Header with Status -->
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                    <?php echo e(strtoupper(substr($organizer->name ?? 'O', 0, 1))); ?>

                </div>
                <div>
                    <h3 class="font-semibold text-gray-900"><?php echo e($organizer->name ?? 'Unknown'); ?></h3>
                    <p class="text-xs text-gray-500"><?php echo e($organizer->email ?? ''); ?></p>
                </div>
            </div>
            
            <?php
                $statusColor = match($organizer->status ?? 'active') {
                    'active' => 'bg-green-100 text-green-800',
                    'inactive' => 'bg-yellow-100 text-yellow-800',
                    'suspended' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                $statusIcon = match($organizer->status ?? 'active') {
                    'active' => 'check-circle',
                    'inactive' => 'clock',
                    'suspended' => 'ban',
                    default => 'question-circle'
                };
            ?>
            <span class="px-2 py-1 <?php echo e($statusColor); ?> rounded text-xs font-medium flex items-center gap-1">
                <i class="fas fa-<?php echo e($statusIcon); ?>"></i>
                <?php echo e(ucfirst($organizer->status ?? 'unknown')); ?>

            </span>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-blue-50 rounded p-3 border-l-2 border-blue-400">
                <p class="text-2xl font-bold text-blue-600"><?php echo e($organizer->events->total ?? 0); ?></p>
                <p class="text-xs text-gray-600">Total Events</p>
            </div>
            <div class="bg-green-50 rounded p-3 border-l-2 border-green-400">
                <p class="text-2xl font-bold text-green-600"><?php echo e(number_format($organizer->events->total_participants ?? 0)); ?></p>
                <p class="text-xs text-gray-600">Participants</p>
            </div>
            <div class="bg-purple-50 rounded p-3 border-l-2 border-purple-400">
                <p class="text-2xl font-bold text-purple-600"><?php echo e(number_format($organizer->events->published ?? 0)); ?></p>
                <p class="text-xs text-gray-600">Published</p>
            </div>
            <div class="bg-amber-50 rounded p-3 border-l-2 border-amber-400">
                <p class="text-2xl font-bold text-amber-600">Rp <?php echo e(number_format(floor(($organizer->events->total_revenue ?? 0) / 1000000))); ?>M</p>
                <p class="text-xs text-gray-600">Revenue</p>
            </div>
        </div>

        <!-- Event Breakdown -->
        <?php
            $breakdown = [
                'draft' => $organizer->events->draft ?? 0,
                'published' => $organizer->events->published ?? 0,
                'completed' => $organizer->events->completed ?? 0,
                'cancelled' => $organizer->events->cancelled ?? 0,
            ];
            $breakdown = array_filter($breakdown, fn($count) => $count > 0);
        ?>
        <?php if(!empty($breakdown)): ?>
        <div class="bg-gray-50 rounded p-3 mb-4 text-xs space-y-1">
            <p class="font-medium text-gray-700 mb-2">Event Status</p>
            <?php $__currentLoopData = $breakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex justify-between">
                <span class="text-gray-600 capitalize"><?php echo e($status); ?>:</span>
                <span class="font-semibold text-gray-800"><?php echo e($count); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="flex gap-2 pt-4 border-t border-gray-200">
            <a href="<?php echo e(route('super.admin.organizer.detail', $organizer->id)); ?>" class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition-colors text-sm font-medium text-center">
                <i class="fas fa-eye mr-1"></i> View
            </a>
            <button type="button" onclick="toggleOrganizer(<?php echo e($organizer->id); ?>)" class="flex-1 px-3 py-2 bg-gray-50 text-gray-600 rounded hover:bg-gray-100 transition-colors text-sm font-medium">
                <i class="fas fa-toggle-on mr-1"></i> Toggle
            </button>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- Pagination -->
<?php if($organizers->hasPages()): ?>
<div class="flex justify-center items-center gap-2 mt-8">
    <?php if($organizers->onFirstPage()): ?>
        <button class="px-3 py-1 text-gray-400 cursor-not-allowed" disabled>Previous</button>
    <?php else: ?>
        <a href="<?php echo e($organizers->previousPageUrl()); ?>" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">Previous</a>
    <?php endif; ?>

    <?php $__currentLoopData = $organizers->getUrlRange(1, $organizers->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($page == $organizers->currentPage()): ?>
            <button class="px-3 py-1 bg-blue-600 text-white rounded font-semibold" disabled><?php echo e($page); ?></button>
        <?php else: ?>
            <a href="<?php echo e($url); ?>" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100"><?php echo e($page); ?></a>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($organizers->hasMorePages()): ?>
        <a href="<?php echo e($organizers->nextPageUrl()); ?>" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">Next</a>
    <?php else: ?>
        <button class="px-3 py-1 text-gray-400 cursor-not-allowed" disabled>Next</button>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<!-- Empty State -->
<div class="bg-white rounded-lg shadow p-8 text-center">
    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
    <h3 class="text-lg font-semibold text-gray-700 mb-2">No Organizers Found</h3>
    <?php if(request('search') || request('status')): ?>
        <p class="text-gray-500 mb-4">No organizers match your search criteria</p>
        <a href="<?php echo e(route('super.admin.organizers')); ?>" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            Clear Filters
        </a>
    <?php else: ?>
        <p class="text-gray-500">There are no event organizers on the platform yet</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    function toggleOrganizer(organizerId) {
        if (confirm('Are you sure you want to toggle this organizer\'s status?')) {
            // This would call an API endpoint to toggle status
            // For now, just show a placeholder
            alert('Toggle organizer ' + organizerId);
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.super-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\frontend\resources\views/admin/super/organizers.blade.php ENDPATH**/ ?>