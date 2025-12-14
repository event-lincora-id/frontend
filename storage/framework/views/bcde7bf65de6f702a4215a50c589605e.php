

<?php $__env->startSection('title', 'User Management'); ?>
<?php $__env->startSection('page-title', 'All Users'); ?>
<?php $__env->startSection('page-description', 'Manage all participants and event organizers on the Event Connect platform'); ?>

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
    <form method="GET" action="<?php echo e(route('super.admin.users')); ?>" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Users</label>
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
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select name="role" id="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                    <option value="">All Roles</option>
                    <option value="participant" <?php echo e(request('role') === 'participant' ? 'selected' : ''); ?>>Participant</option>
                    <option value="admin" <?php echo e(request('role') === 'admin' ? 'selected' : ''); ?>>Event Organizer</option>
                    <option value="super_admin" <?php echo e(request('role') === 'super_admin' ? 'selected' : ''); ?>>Super Admin</option>
                </select>
            </div>

            <div class="flex gap-2 items-end">
                <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <?php if(request('search') || request('role')): ?>
                <a href="<?php echo e(route('super.admin.users')); ?>" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Clear
                </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if(request('search') || request('role')): ?>
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
        <i class="fas fa-info-circle mr-2"></i>
        Filtering users
        <?php echo e(request('search') ? "with name/email '" . request('search') . "'" : ''); ?>

        <?php echo e(request('search') && request('role') ? ' and ' : ''); ?>

        <?php echo e(request('role') ? "role '" . request('role') . "'" : ''); ?>

    </div>
    <?php endif; ?>
</div>

<!-- Results Counter -->
<div class="mb-4 text-sm text-gray-600">
    <?php if($users->count() > 0): ?>
        <span class="font-semibold text-gray-800">Showing <?php echo e($users->firstItem()); ?> to <?php echo e($users->lastItem()); ?> of <?php echo e($users->total()); ?> users</span>
    <?php else: ?>
        <?php if(request('search') || request('role')): ?>
            <span class="text-gray-500">No users found matching your criteria</span>
        <?php else: ?>
            <span class="text-gray-500">No users yet</span>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Users Table (Responsive) -->
<?php if($users->count() > 0): ?>
<div class="bg-white rounded-lg shadow overflow-hidden mb-6">
    <!-- Desktop View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                <?php echo e(strtoupper(substr($user->name ?? 'U', 0, 1))); ?>

                            </div>
                            <div>
                                <p class="font-medium text-gray-900"><?php echo e($user->name ?? 'Unknown'); ?></p>
                                <p class="text-xs text-gray-500">ID: <?php echo e($user->id ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-gray-900"><?php echo e($user->email ?? '-'); ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <?php
                            $roleColor = match($user->role ?? 'participant') {
                                'admin' => 'bg-purple-100 text-purple-800',
                                'super_admin' => 'bg-red-100 text-red-800',
                                'participant' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $roleLabel = match($user->role ?? 'participant') {
                                'admin' => 'Event Organizer',
                                'super_admin' => 'Super Admin',
                                'participant' => 'Participant',
                                default => 'Unknown'
                            };
                        ?>
                        <span class="px-3 py-1 <?php echo e($roleColor); ?> rounded-full text-xs font-medium inline-flex items-center gap-1">
                            <?php echo e($roleLabel); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-gray-900"><?php echo e(date('d M Y', strtotime($user->created_at ?? now()))); ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <?php
                            $statusColor = match($user->status ?? 'active') {
                                'active' => 'bg-green-100 text-green-800',
                                'inactive' => 'bg-yellow-100 text-yellow-800',
                                'suspended' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        ?>
                        <span class="px-3 py-1 <?php echo e($statusColor); ?> rounded-full text-xs font-medium">
                            <?php echo e(ucfirst($user->status ?? 'unknown')); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="#" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile View -->
    <div class="md:hidden space-y-3 p-4">
        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-gradient-to-r from-white to-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-gray-900"><?php echo e($user->name ?? 'Unknown'); ?></h3>
                    <p class="text-xs text-gray-500"><?php echo e($user->email ?? '-'); ?></p>
                </div>
                <?php
                    $roleColor = match($user->role ?? 'participant') {
                        'admin' => 'bg-purple-100 text-purple-800',
                        'super_admin' => 'bg-red-100 text-red-800',
                        'participant' => 'bg-blue-100 text-blue-800',
                        default => 'bg-gray-100 text-gray-800'
                    };
                    $roleLabel = match($user->role ?? 'participant') {
                        'admin' => 'EO',
                        'super_admin' => 'Super Admin',
                        'participant' => 'Participant',
                        default => 'Unknown'
                    };
                ?>
                <span class="px-2 py-1 <?php echo e($roleColor); ?> rounded text-xs font-medium">
                    <?php echo e($roleLabel); ?>

                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 mb-3 text-xs">
                <div>
                    <p class="text-gray-600">Joined</p>
                    <p class="font-semibold text-gray-900"><?php echo e(date('d M Y', strtotime($user->created_at ?? now()))); ?></p>
                </div>
                <div>
                    <?php
                        $statusColor = match($user->status ?? 'active') {
                            'active' => 'text-green-600',
                            'inactive' => 'text-yellow-600',
                            'suspended' => 'text-red-600',
                            default => 'text-gray-600'
                        };
                    ?>
                    <p class="text-gray-600">Status</p>
                    <p class="font-semibold <?php echo e($statusColor); ?>"><?php echo e(ucfirst($user->status ?? 'unknown')); ?></p>
                </div>
            </div>

            <button type="button" class="w-full text-center px-3 py-2 bg-blue-50 text-blue-600 rounded text-sm font-medium hover:bg-blue-100 transition-colors">
                <i class="fas fa-eye mr-1"></i> View Details
            </button>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<!-- Pagination -->
<?php if($users->hasPages()): ?>
<div class="flex justify-center items-center gap-2">
    <?php if($users->onFirstPage()): ?>
        <button class="px-3 py-1 text-gray-400 cursor-not-allowed" disabled>Previous</button>
    <?php else: ?>
        <a href="<?php echo e($users->previousPageUrl()); ?>" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">Previous</a>
    <?php endif; ?>

    <?php $__currentLoopData = $users->getUrlRange(1, $users->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($page == $users->currentPage()): ?>
            <button class="px-3 py-1 bg-blue-600 text-white rounded font-semibold" disabled><?php echo e($page); ?></button>
        <?php else: ?>
            <a href="<?php echo e($url); ?>" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100"><?php echo e($page); ?></a>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($users->hasMorePages()): ?>
        <a href="<?php echo e($users->nextPageUrl()); ?>" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">Next</a>
    <?php else: ?>
        <button class="px-3 py-1 text-gray-400 cursor-not-allowed" disabled>Next</button>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<!-- Empty State -->
<div class="bg-white rounded-lg shadow p-8 text-center">
    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
    <h3 class="text-lg font-semibold text-gray-700 mb-2">No Users Found</h3>
    <?php if(request('search') || request('role')): ?>
        <p class="text-gray-500 mb-4">No users match your search criteria</p>
        <a href="<?php echo e(route('super.admin.users')); ?>" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            Clear Filters
        </a>
    <?php else: ?>
        <p class="text-gray-500">There are no users on the platform yet</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.super-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\frontend\resources\views/admin/super/users.blade.php ENDPATH**/ ?>