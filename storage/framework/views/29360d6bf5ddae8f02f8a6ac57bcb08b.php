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

    <!-- Search and Filter -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" placeholder="Search users..." class="admin-input w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div class="flex gap-2">
                <select class="admin-input px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="participant">Participant</option>
                </select>
                <select class="admin-input px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="overflow-x-auto">
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
                                <?php if($user->avatar): ?>
                                    <img class="h-10 w-10 rounded-full" src="<?php echo e($user->avatar); ?>" alt="<?php echo e($user->name); ?>">
                                <?php else: ?>
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($user->name); ?></div>
                                <div class="text-sm text-gray-500"><?php echo e($user->email); ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'); ?>">
                            <?php echo e(ucfirst($user->role)); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo e($user->events_count ?? 0); ?> events
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo e($user->created_at->format('M d, Y')); ?>

                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="<?php echo e(route('admin.users.show', $user)); ?>" style="color: var(--color-primary);" class="hover:opacity-80">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="text-gray-600 hover:text-gray-900">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="text-red-600 hover:text-red-900" onclick="confirmDelete(<?php echo e($user->id); ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No users found
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

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete User</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete this user? This action cannot be undone.</p>
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
function confirmDelete(userId) {
    document.getElementById('deleteForm').action = `/admin/users/${userId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Study\Kuliah\Semester-7\CP\event-connect\resources\views/admin/users/index.blade.php ENDPATH**/ ?>