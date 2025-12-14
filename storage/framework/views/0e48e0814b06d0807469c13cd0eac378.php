

<?php $__env->startSection('title', 'Platform Events Management'); ?>
<?php $__env->startSection('page-title', 'All Events'); ?>
<?php $__env->startSection('page-description', 'Monitor and manage all events across the Event Connect platform'); ?>

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
    <form method="GET" action="<?php echo e(route('super.admin.events')); ?>" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Events</label>
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        id="search" 
                        placeholder="Search by title..."
                        value="<?php echo e(request('search')); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                    <option value="">All Status</option>
                    <option value="published" <?php echo e(request('status') === 'published' ? 'selected' : ''); ?>>Published</option>
                    <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Draft</option>
                    <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Completed</option>
                    <option value="cancelled" <?php echo e(request('status') === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                </select>
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category_id" id="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                    <option value="">All Categories</option>
                    <option value="1" <?php echo e(request('category_id') === '1' ? 'selected' : ''); ?>>Technology</option>
                    <option value="2" <?php echo e(request('category_id') === '2' ? 'selected' : ''); ?>>Business</option>
                    <option value="3" <?php echo e(request('category_id') === '3' ? 'selected' : ''); ?>>Workshop</option>
                    <option value="4" <?php echo e(request('category_id') === '4' ? 'selected' : ''); ?>>Conference</option>
                </select>
            </div>

            <div class="flex gap-2 items-end">
                <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <?php if(request('search') || request('status') || request('category_id')): ?>
                <a href="<?php echo e(route('super.admin.events')); ?>" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Clear
                </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if(request('search') || request('status') || request('category_id')): ?>
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
        <i class="fas fa-info-circle mr-2"></i>
        Filtering events
        <?php echo e(request('search') ? "with title '" . request('search') . "'" : ''); ?>

        <?php echo e(request('status') && request('search') ? ' and ' : ''); ?>

        <?php echo e(request('status') ? "status '" . request('status') . "'" : ''); ?>

        <?php echo e((request('status') || request('search')) && request('category_id') ? ' and ' : ''); ?>

        <?php echo e(request('category_id') ? "category '" . request('category_id') . "'" : ''); ?>

    </div>
    <?php endif; ?>
</div>

<!-- Results Counter -->
<div class="mb-4 text-sm text-gray-600">
    <?php if($events->count() > 0): ?>
        <span class="font-semibold text-gray-800">Showing <?php echo e($events->firstItem()); ?> to <?php echo e($events->lastItem()); ?> of <?php echo e($events->total()); ?> events</span>
    <?php else: ?>
        <?php if(request('search') || request('status') || request('category_id')): ?>
            <span class="text-gray-500">No events found matching your criteria</span>
        <?php else: ?>
            <span class="text-gray-500">No events yet</span>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Events Table (Responsive) -->
<?php if($events->count() > 0): ?>
<div class="bg-white rounded-lg shadow overflow-hidden mb-6">
    <!-- Desktop View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Organizer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Participants</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="toggleEventDetails(<?php echo e($event->id); ?>)">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-600 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                <?php echo e(strtoupper(substr($event->title ?? 'E', 0, 1))); ?>

                            </div>
                            <div>
                                <p class="font-medium text-gray-900 line-clamp-1"><?php echo e($event->title ?? 'Untitled Event'); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e(is_object($event->category ?? null) ? ($event->category->name ?? 'Uncategorized') : ($event->category ?? 'Uncategorized')); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-gray-900"><?php echo e(is_object($event->organizer ?? null) ? ($event->organizer->name ?? 'Unknown') : ($event->organizer_name ?? 'Unknown')); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e(is_object($event->organizer ?? null) ? ($event->organizer->email ?? '') : ($event->organizer_email ?? '')); ?></p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-gray-900"><?php echo e(date('d M Y', strtotime($event->start_date ?? now()))); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e(date('H:i', strtotime($event->start_date ?? now()))); ?></p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-center">
                            <p class="font-bold text-lg text-blue-600"><?php echo e($event->participants->total ?? ($event->participant_count ?? 0)); ?></p>
                            <p class="text-xs text-gray-500">/ <?php echo e($event->quota ?? 'Unlimited'); ?></p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <?php
                            $statusColor = match($event->status ?? 'draft') {
                                'published' => 'bg-green-100 text-green-800',
                                'draft' => 'bg-yellow-100 text-yellow-800',
                                'completed' => 'bg-blue-100 text-blue-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $statusIcon = match($event->status ?? 'draft') {
                                'published' => 'check-circle',
                                'draft' => 'edit',
                                'completed' => 'check-double',
                                'cancelled' => 'times-circle',
                                default => 'question-circle'
                            };
                        ?>
                        <span class="px-3 py-1 <?php echo e($statusColor); ?> rounded-full text-xs font-medium inline-flex items-center gap-1">
                            <i class="fas fa-<?php echo e($statusIcon); ?>"></i>
                            <?php echo e(ucfirst($event->status ?? 'unknown')); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="#" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                <!-- Expandable Details Row -->
                <tr id="event-details-<?php echo e($event->id); ?>" class="bg-gradient-to-r from-gray-50 to-white hidden">
                    <td colspan="6" class="px-6 py-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 rounded p-3 border-l-2 border-blue-400">
                                <p class="text-2xl font-bold text-blue-600"><?php echo e($event->participants->total ?? 0); ?></p>
                                <p class="text-xs text-gray-600">Participants</p>
                            </div>
                            <div class="bg-green-50 rounded p-3 border-l-2 border-green-400">
                                <p class="text-2xl font-bold text-green-600"><?php echo e($event->participants->registered ?? 0); ?></p>
                                <p class="text-xs text-gray-600">Registered</p>
                            </div>
                            <div class="bg-amber-50 rounded p-3 border-l-2 border-amber-400">
                                <p class="text-2xl font-bold text-amber-600">Rp <?php echo e(number_format($event->revenue ?? 0)); ?></p>
                                <p class="text-xs text-gray-600">Revenue</p>
                            </div>
                            <div class="bg-purple-50 rounded p-3 border-l-2 border-purple-400">
                                <p class="text-2xl font-bold text-purple-600"><?php echo e($event->participants->attended ?? 0); ?></p>
                                <p class="text-xs text-gray-600">Attended</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-3 line-clamp-2"><?php echo e($event->description ?? 'No description'); ?></p>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile View -->
    <div class="md:hidden space-y-3 p-4">
        <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-gradient-to-r from-white to-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-gray-900"><?php echo e($event->title ?? 'Untitled Event'); ?></h3>
                    <p class="text-xs text-gray-500"><?php echo e(is_object($event->organizer ?? null) ? ($event->organizer->name ?? 'Unknown') : ($event->organizer_name ?? 'Unknown')); ?></p>
                </div>
                <?php
                    $statusColor = match($event->status ?? 'draft') {
                        'published' => 'bg-green-100 text-green-800',
                        'draft' => 'bg-yellow-100 text-yellow-800',
                        'completed' => 'bg-blue-100 text-blue-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800'
                    };
                ?>
                <span class="px-2 py-1 <?php echo e($statusColor); ?> rounded text-xs font-medium">
                    <?php echo e(ucfirst($event->status ?? 'unknown')); ?>

                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 mb-3">
                <div class="bg-blue-50 rounded p-2 border-l-2 border-blue-400 text-center">
                    <p class="font-bold text-blue-600"><?php echo e($event->participants->total ?? 0); ?></p>
                    <p class="text-xs text-gray-600">Participants</p>
                </div>
                <div class="bg-amber-50 rounded p-2 border-l-2 border-amber-400 text-center">
                    <p class="font-bold text-amber-600">Rp <?php echo e(number_format(floor(($event->revenue ?? 0) / 1000))); ?>K</p>
                    <p class="text-xs text-gray-600">Revenue</p>
                </div>
            </div>

            <p class="text-xs text-gray-600 mb-3">
                <i class="fas fa-calendar mr-1"></i>
                <?php echo e(date('d M Y, H:i', strtotime($event->start_date ?? now()))); ?>

            </p>

            <button type="button" onclick="toggleEventDetailsMobile(<?php echo e($event->id); ?>)" class="w-full text-center px-3 py-2 bg-blue-50 text-blue-600 rounded text-sm font-medium hover:bg-blue-100 transition-colors">
                <i class="fas fa-chevron-down"></i> View Details
            </button>
        </div>
        
        <!-- Mobile Expandable Details -->
        <div id="event-details-mobile-<?php echo e($event->id); ?>" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-2">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <p class="text-xs text-gray-600">Registered</p>
                    <p class="font-bold text-green-600"><?php echo e($event->participants->registered ?? 0); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Attended</p>
                    <p class="font-bold text-purple-600"><?php echo e($event->participants->attended ?? 0); ?></p>
                </div>
            </div>
            <p class="text-xs text-gray-700"><?php echo e($event->description ?? 'No description'); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<!-- Pagination -->
<?php if($events->hasPages()): ?>
<div class="flex justify-center items-center gap-2">
    <?php if($events->onFirstPage()): ?>
        <button class="px-3 py-1 text-gray-400 cursor-not-allowed" disabled>Previous</button>
    <?php else: ?>
        <a href="<?php echo e($events->previousPageUrl()); ?>" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">Previous</a>
    <?php endif; ?>

    <?php $__currentLoopData = $events->getUrlRange(1, $events->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($page == $events->currentPage()): ?>
            <button class="px-3 py-1 bg-blue-600 text-white rounded font-semibold" disabled><?php echo e($page); ?></button>
        <?php else: ?>
            <a href="<?php echo e($url); ?>" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100"><?php echo e($page); ?></a>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($events->hasMorePages()): ?>
        <a href="<?php echo e($events->nextPageUrl()); ?>" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">Next</a>
    <?php else: ?>
        <button class="px-3 py-1 text-gray-400 cursor-not-allowed" disabled>Next</button>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<!-- Empty State -->
<div class="bg-white rounded-lg shadow p-8 text-center">
    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
    <h3 class="text-lg font-semibold text-gray-700 mb-2">No Events Found</h3>
    <?php if(request('search') || request('status') || request('category_id')): ?>
        <p class="text-gray-500 mb-4">No events match your search criteria</p>
        <a href="<?php echo e(route('super.admin.events')); ?>" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            Clear Filters
        </a>
    <?php else: ?>
        <p class="text-gray-500">There are no events on the platform yet</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    function toggleEventDetails(eventId) {
        const row = document.getElementById('event-details-' + eventId);
        if (row) {
            row.classList.toggle('hidden');
        }
    }

    function toggleEventDetailsMobile(eventId) {
        const row = document.getElementById('event-details-mobile-' + eventId);
        if (row) {
            row.classList.toggle('hidden');
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.super-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\frontend\resources\views/admin/super/events.blade.php ENDPATH**/ ?>