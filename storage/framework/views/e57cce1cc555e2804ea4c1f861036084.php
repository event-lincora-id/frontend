<?php $__env->startSection('title', 'Event Participants'); ?>
<?php $__env->startSection('page-title', 'Event Participants'); ?>
<?php $__env->startSection('page-description', 'Manage participants who joined your events'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Event Participants</h3>
            <div class="text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Users who joined your events
            </div>
        </div>
    </div>

    <?php if(session('error')): ?>
    <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo e(session('error')); ?>

    </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
    <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        <i class="fas fa-exclamation-circle mr-2"></i><?php echo e($error); ?>

    </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="px-6 py-4 border-b border-gray-200">
        <form method="GET" action="<?php echo e(route('admin.users.index')); ?>" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       id="searchInput"
                       value="<?php echo e(request('search')); ?>" 
                       placeholder="Search by name or email..." 
                       class="admin-input w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" 
                        style="background-color: #B22234;" 
                        class="px-4 py-2 text-white rounded-md hover:opacity-90 transition-opacity">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <?php if(request('search')): ?>
                <a href="<?php echo e(route('admin.users.index')); ?>" 
                   class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
                <?php endif; ?>
            </div>
        </form>
        <?php if(request('search')): ?>
        <div class="mt-3 text-sm text-gray-600">
            <i class="fas fa-info-circle mr-1"></i>
            Showing results for: <strong>"<?php echo e(request('search')); ?>"</strong>
        </div>
        <?php endif; ?>
    </div>

    <!-- Users Table -->
    <div class="overflow-x-auto">
        <?php if($users->total() > 0): ?>
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <span class="text-sm text-gray-600">
                Showing <strong><?php echo e($users->firstItem()); ?></strong> to <strong><?php echo e($users->lastItem()); ?></strong> of <strong><?php echo e($users->total()); ?></strong> participant<?php echo e($users->total() !== 1 ? 's' : ''); ?>

            </span>
        </div>
        <?php endif; ?>
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <?php if($user->avatar ?? null): ?>
                                    <img class="h-10 w-10 rounded-full" src="<?php echo e($user->avatar); ?>" alt="<?php echo e($user->full_name ?? $user->name); ?>">
                                <?php else: ?>
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($user->full_name ?? $user->name); ?></div>
                                <div class="text-sm text-gray-500"><?php echo e($user->email); ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e(($user->role ?? 'participant') === 'admin' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'); ?>">
                            <?php echo e(ucfirst($user->role ?? 'Participant')); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php
                            $participationCount = 0;
                            if (isset($user->event_participations) && is_array($user->event_participations)) {
                                $participationCount = count($user->event_participations);
                            } elseif (isset($user->events) && is_array($user->events)) {
                                $participationCount = count($user->events);
                            }
                        ?>
                        <?php echo e($participationCount); ?> event<?php echo e($participationCount !== 1 ? 's' : ''); ?> joined
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php if(isset($user->created_at)): ?>
                            <?php
                                try {
                                    $date = new \DateTime($user->created_at);
                                    echo $date->format('M d, Y');
                                } catch (\Exception $e) {
                                    echo $user->created_at;
                                }
                            ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <?php if($participationCount > 0): ?>
                                <button onclick="toggleUserEvents(<?php echo e($user->id); ?>)" class="text-purple-600 hover:text-purple-900" title="View Events">
                                    <i class="fas fa-list"></i>
                                </button>
                            <?php endif; ?>
                            <a href="<?php echo e(route('admin.users.show', $user->id)); ?>" style="color: var(--color-primary);" class="hover:opacity-80" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                
                <!-- Expandable row for user events -->
                <?php if($participationCount > 0): ?>
                <tr id="user-events-<?php echo e($user->id); ?>" class="hidden bg-gray-50">
                    <td colspan="6" class="px-6 py-4">
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Events Joined by <?php echo e($user->full_name ?? $user->name); ?>

                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php
                                    $events = $user->events ?? $user->event_participations ?? [];
                                ?>
                                <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $participation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        // Handle both direct event object and participation object
                                        $event = $participation->event ?? $participation;
                                        $status = $participation->status ?? 'registered';
                                        $isPaid = $participation->is_paid ?? $participation->payment_status ?? false;
                                        $attendedAt = $participation->attended_at ?? null;
                                    ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <h5 class="font-semibold text-gray-900 mb-2"><?php echo e($event->title ?? 'Event'); ?></h5>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex items-center text-gray-600">
                                                <i class="fas fa-map-marker-alt w-4 mr-2"></i>
                                                <span><?php echo e($event->location ?? 'N/A'); ?></span>
                                            </div>
                                            <div class="flex items-center text-gray-600">
                                                <i class="fas fa-calendar w-4 mr-2"></i>
                                                <span>
                                                    <?php if(isset($event->start_date)): ?>
                                                        <?php
                                                            try {
                                                                $startDate = new \DateTime($event->start_date);
                                                                echo $startDate->format('M d, Y');
                                                            } catch (\Exception $e) {
                                                                echo $event->start_date;
                                                            }
                                                        ?>
                                                    <?php else: ?>
                                                        N/A
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <div class="flex items-center text-gray-600">
                                                <i class="fas fa-tag w-4 mr-2"></i>
                                                <span><?php echo e($event->category->name ?? 'Uncategorized'); ?></span>
                                            </div>
                                            <div class="flex items-center justify-between pt-2 border-t">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                    <?php echo e($status === 'attended' ? 'bg-green-100 text-green-800' : 
                                                       ($status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')); ?>">
                                                    <?php echo e(ucfirst($status)); ?>

                                                </span>
                                                <?php if($isPaid || ($isPaid === 'paid')): ?>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i>Paid
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if($attendedAt): ?>
                                                <div class="flex items-center text-gray-500 text-xs pt-1">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Attended: 
                                                    <?php
                                                        try {
                                                            $attended = new \DateTime($attendedAt);
                                                            echo $attended->format('M d, Y H:i');
                                                        } catch (\Exception $e) {
                                                            echo $attendedAt;
                                                        }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-users-slash text-gray-400 text-5xl mb-4"></i>
                            <?php if(request('search')): ?>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No participants found</h3>
                                <p class="text-gray-500 mb-4">
                                    No participants match your search "<strong><?php echo e(request('search')); ?></strong>"
                                </p>
                                <a href="<?php echo e(route('admin.users.index')); ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                                    <i class="fas fa-times mr-2"></i>Clear Search
                                </a>
                            <?php else: ?>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No participants yet</h3>
                                <p class="text-gray-500">
                                    No one has joined your events yet.
                                </p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if($users->hasPages()): ?>
    <div class="px-6 py-4 border-t border-gray-200">
        <?php echo e($users->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function toggleUserEvents(userId) {
    const eventRow = document.getElementById(`user-events-${userId}`);
    const button = document.querySelector(`button[onclick="toggleUserEvents(${userId})"]`);
    
    if (eventRow.classList.contains('hidden')) {
        eventRow.classList.remove('hidden');
        button.innerHTML = '<i class="fas fa-chevron-up"></i>';
        button.title = 'Hide Events';
    } else {
        eventRow.classList.add('hidden');
        button.innerHTML = '<i class="fas fa-list"></i>';
        button.title = 'View Events';
    }
}

// Auto-focus search input if there's a search query
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const urlParams = new URLSearchParams(window.location.search);
    
    // Focus search input if user just searched
    if (urlParams.has('search') && searchInput) {
        searchInput.focus();
        // Place cursor at end of text
        const val = searchInput.value;
        searchInput.value = '';
        searchInput.value = val;
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\frontend\resources\views/admin/users/index.blade.php ENDPATH**/ ?>