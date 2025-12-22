<?php $__env->startSection('title', 'My Events'); ?>
<?php $__env->startSection('page-title', 'My Events'); ?>
<?php $__env->startSection('page-description', 'Manage your created events'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">All Events</h3>
            <a href="<?php echo e(route('admin.events.create')); ?>" style="background-color: var(--color-primary);" class="text-white px-6 py-3 rounded-lg hover:opacity-90 font-medium shadow-md hover:shadow-lg transition-all duration-200 flex items-center">
                <i class="fas fa-plus mr-2"></i>Create New Event
            </a>
        </div>
    </div>

    <?php if(isset($error)): ?>
        <div class="p-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?php echo e($error); ?>

            </div>
        </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="px-6 py-4 border-b border-gray-200">
        <form method="GET" action="<?php echo e(route('admin.events.index')); ?>">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search events..." class="admin-input w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="flex gap-2">
                    <select name="category_id" class="admin-input px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">All Categories</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category_id') == $cat->id ? 'selected' : ''); ?>>
                                <?php echo e($cat->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="status" class="admin-input px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">All Status</option>
                        <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="published" <?php echo e(request('status') == 'published' ? 'selected' : ''); ?>>Published</option>
                        <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Completed</option>
                        <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Events Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organizer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participants</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                <?php if(isset($event->image_url) && $event->image_url): ?>
                                    <img class="h-12 w-12 rounded-lg object-cover" src="<?php echo e($event->image_url); ?>" alt="<?php echo e($event->title); ?>">
                                <?php else: ?>
                                    <div class="h-12 w-12 rounded-lg bg-gray-300 flex items-center justify-center">
                                        <i class="fas fa-calendar text-gray-600"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4 max-w-xs">
                                <div class="text-sm font-medium text-gray-900 truncate"><?php echo e($event->title ?? 'Untitled'); ?></div>
                                <div class="text-xs text-gray-500">zoom</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            <?php if(isset($event->organizer)): ?>
                                <?php echo e(is_object($event->organizer) ? ($event->organizer->name ?? $event->organizer->full_name ?? 'Unknown') : ($event->organizer['name'] ?? $event->organizer['full_name'] ?? 'Unknown')); ?>

                            <?php else: ?>
                                Unknown Organizer
                            <?php endif; ?>
                        </div>
                        <div class="text-xs text-gray-500">
                            <?php if(isset($event->organizer)): ?>
                                <?php echo e(is_object($event->organizer) ? ($event->organizer->email ?? '') : ($event->organizer['email'] ?? '')); ?>

                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if(isset($event->category)): ?>
                            <?php
                                $categoryName = is_object($event->category) ? ($event->category->name ?? 'Uncategorized') : ($event->category['name'] ?? 'Uncategorized');
                                $categoryColor = is_object($event->category) ? ($event->category->color ?? '#6B7280') : ($event->category['color'] ?? '#6B7280');
                            ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium" style="background-color: <?php echo e($categoryColor); ?>20; color: <?php echo e($categoryColor); ?>">
                                <span class="w-2 h-2 rounded-full mr-1.5" style="background-color: <?php echo e($categoryColor); ?>"></span>
                                <?php echo e($categoryName); ?>

                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                Uncategorized
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php if(isset($event->start_date)): ?>
                            <div class="font-medium"><?php echo e(\Carbon\Carbon::parse($event->start_date)->format('M d, Y')); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e(\Carbon\Carbon::parse($event->start_date)->format('h:i A')); ?> - <?php echo e(isset($event->end_date) ? \Carbon\Carbon::parse($event->end_date)->format('h:i A') : 'N/A'); ?></div>
                        <?php else: ?>
                            <div class="text-gray-400">No date</div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center">
                            <div class="text-gray-900 font-medium"><?php echo e($event->participants_count ?? $event->registered_count ?? 0); ?>/<?php echo e($event->quota ?? $event->max_participants ?? '∞'); ?></div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-3">
                            <a href="<?php echo e(route('admin.events.show', $event->id)); ?>" class="text-blue-600 hover:text-blue-800" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo e(route('admin.events.edit', $event->id)); ?>" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?php echo e(route('admin.events.participants', $event->id)); ?>" class="text-green-600 hover:text-green-800" title="Participants">
                                <i class="fas fa-users"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo e($event->id); ?>)" class="text-red-600 hover:text-red-800" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-calendar-alt text-4xl mb-4 text-gray-400"></i>
                        <div class="text-lg font-medium">No events found</div>
                        <p class="mt-2">Create your first event to get started!</p>
                        <a href="<?php echo e(route('admin.events.create')); ?>" style="background-color: var(--color-primary);" class="inline-block mt-4 text-white px-6 py-2 rounded-lg hover:opacity-90">
                            <i class="fas fa-plus mr-2"></i>Create Event
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if($events->hasPages()): ?>
    <div class="px-6 py-4 border-t border-gray-200">
        <?php echo e($events->links()); ?>

    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Event</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete this event? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                </form>
                <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function confirmDelete(eventId) {
    document.getElementById('deleteForm').action = '/admin/events/' + eventId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Study\Kuliah\Semester-7\CP\event-connect\resources\views/admin/events/index.blade.php ENDPATH**/ ?>