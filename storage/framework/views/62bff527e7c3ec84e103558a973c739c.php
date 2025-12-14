<?php $__env->startSection('title', 'Event Details'); ?>
<?php $__env->startSection('page-title', 'Event Details'); ?>
<?php $__env->startSection('page-description', 'View event information and participants'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900"><?php echo e($event->title ?? 'Event Details'); ?></h3>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('admin.events.edit', $event->id)); ?>" style="background-color: var(--color-primary);" class="text-white px-4 py-2 rounded-md hover:opacity-90">
                    <i class="fas fa-edit mr-2"></i>Edit Event
                </a>
                <a href="<?php echo e(route('admin.events.feedbacks', $event->id)); ?>" style="background-color: #9333ea !important; color: white !important; display: inline-block !important;" class="px-4 py-2 rounded-md hover:bg-purple-700">
                    <i class="fas fa-comments mr-2"></i>View Feedbacks
                </a>
                <a href="<?php echo e(route('admin.events.index')); ?>" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Events
                </a>
            </div>
        </div>
    </div>

    <!-- Event Details -->
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Event Information -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Event Information</h4>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo e($event->title); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo e($event->description); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <?php echo e($event->category->name ?? 'Uncategorized'); ?>

                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo e($event->location); ?></p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo e(\Carbon\Carbon::parse($event->start_date)->format('M d, Y h:i A')); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo e(\Carbon\Carbon::parse($event->end_date)->format('M d, Y h:i A')); ?></p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Max Participants</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo e($event->max_participants ?? 'Unlimited'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price</label>
                            <p class="mt-1 text-sm text-gray-900">Rp <?php echo e(number_format($event->price, 0, ',', '.')); ?></p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <?php
                            $now = now();
                            $startDate = \Carbon\Carbon::parse($event->start_date);
                            $endDate = \Carbon\Carbon::parse($event->end_date);
                            
                            if ($now < $startDate) {
                                $status = 'upcoming';
                                $statusClass = 'bg-blue-100 text-blue-800';
                            } elseif ($now >= $startDate && $now <= $endDate) {
                                $status = 'ongoing';
                                $statusClass = 'bg-green-100 text-green-800';
                            } else {
                                $status = 'completed';
                                $statusClass = 'bg-gray-100 text-gray-800';
                            }
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($statusClass); ?>">
                            <?php echo e(ucfirst($status)); ?>

                        </span>
                    </div>
                </div>
            </div>

            <!-- Event Image -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Event Image</h4>
                <?php if($event->image): ?>
                    <img class="w-full h-64 object-cover rounded-lg" src="<?php echo e($event->image_url ?? asset('storage/' . $event->image)); ?>" alt="<?php echo e($event->title); ?>">
                <?php else: ?>
                    <div class="w-full h-64 bg-gray-300 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar text-gray-600 text-4xl"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Participants Section -->
        <div class="mt-8">
            <?php
                $participantsCollection = collect($participants ?? []);
            ?>

            <h4 class="text-lg font-medium text-gray-900 mb-4">Participants (<?php echo e($participantsCollection->count()); ?>)</h4>
            
            <?php if($participantsCollection->count() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $participantsCollection; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $participant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo e($participant->user->full_name ?? $participant->user->name ?? 'Unknown'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($participant->user->email ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?php if(($participant->status ?? '') == 'registered'): ?> bg-blue-100 text-blue-800
                                        <?php elseif(($participant->status ?? '') == 'attended'): ?> bg-green-100 text-green-800
                                        <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                                        <?php echo e(ucfirst($participant->status ?? 'pending')); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e(isset($participant->created_at) ? \Carbon\Carbon::parse($participant->created_at)->format('M d, Y h:i A') : '-'); ?>

                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-users text-4xl mb-4"></i>
                    <p>No participants yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\frontend\resources\views/admin/events/show.blade.php ENDPATH**/ ?>