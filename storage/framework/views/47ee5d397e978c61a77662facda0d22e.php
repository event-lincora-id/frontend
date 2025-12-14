<?php $__env->startSection('title', 'Event Feedbacks'); ?>
<?php $__env->startSection('page-title', 'Event Feedbacks'); ?>
<?php $__env->startSection('page-description', 'View feedback and AI summary for this event'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900"><?php echo e($event->title ?? 'Event Feedbacks'); ?></h2>
            <p class="text-gray-600 mt-1">Manage and analyze participant feedback</p>
        </div>
        <div class="flex space-x-2">
            <?php if(count($feedbacks) > 0): ?>
                <form action="<?php echo e(route('admin.events.generate-summary', $event->id)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" style="background-color: #9333ea !important; color: white !important;" class="px-4 py-2 rounded-lg hover:opacity-90 flex items-center">
                        <i class="fas fa-robot mr-2"></i>
                        <?php echo e($summary ? 'Regenerate' : 'Generate'); ?> AI Summary
                    </button>
                </form>
            <?php endif; ?>
            <a href="<?php echo e(route('admin.events.show', $event->id)); ?>" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Back to Event
            </a>
        </div>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<!-- AI Summary Card -->
<?php if($summary): ?>
<div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg shadow-lg mb-6 border border-purple-200">
    <div class="px-6 py-4 border-b border-purple-200 bg-white bg-opacity-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-robot text-purple-600 text-2xl mr-3"></i>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">AI-Generated Summary</h3>
                    <p class="text-sm text-gray-600">
                        Generated <?php echo e(isset($summary->generated_at) ? \Carbon\Carbon::parse($summary->generated_at)->diffForHumans() : 'recently'); ?>

                        <?php if(isset($summary->feedback_count)): ?>
                            · Based on <?php echo e($summary->feedback_count); ?> feedback(s)
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <button onclick="toggleSummary()" class="text-purple-600 hover:text-purple-800 focus:outline-none flex items-center gap-2 px-3 py-2 rounded-md hover:bg-purple-100 transition-colors">
                <span id="toggleText" class="text-sm font-medium">Hide</span>
                <i id="toggleIcon" class="fas fa-chevron-up"></i>
            </button>
        </div>
    </div>
    <div id="summaryContent" class="p-6">
        <div class="prose max-w-none text-gray-700 leading-relaxed" style="white-space: pre-line;">
            <?php
                $summaryText = $summary->summary ?? $event->feedback_summary;
                // Convert markdown to HTML
                $summaryText = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $summaryText); // **bold**
                $summaryText = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $summaryText); // *italic*
                $summaryText = preg_replace('/^- (.+)$/m', '<li>$1</li>', $summaryText); // - bullet points
                $summaryText = preg_replace('/(<li>.*<\/li>)/s', '<ul class="list-disc ml-6 my-2">$1</ul>', $summaryText); // wrap in ul
                $summaryText = nl2br($summaryText); // preserve line breaks
            ?>
            <?php echo $summaryText; ?>

        </div>
    </div>
</div>

<script>
function toggleSummary() {
    const content = document.getElementById('summaryContent');
    const icon = document.getElementById('toggleIcon');
    const text = document.getElementById('toggleText');

    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
        text.textContent = 'Hide';
    } else {
        content.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
        text.textContent = 'Show';
    }
}
</script>
<?php endif; ?>

<!-- Statistics Cards -->
<?php if($stats): ?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <!-- Average Rating -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Average Rating</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo e(number_format($stats->average_rating ?? 0, 1)); ?></p>
                <div class="flex items-center mt-2">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if($i <= round($stats->average_rating ?? 0)): ?>
                            <i class="fas fa-star text-yellow-400"></i>
                        <?php else: ?>
                            <i class="far fa-star text-gray-300"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-star text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Feedbacks -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Feedbacks</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo e($stats->total_feedbacks ?? count($feedbacks)); ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-comments text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- 5 Star Count -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">5-Star Ratings</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo e($stats->rating_5 ?? 0); ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-thumbs-up text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Low Rating Count -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">1-2 Star Ratings</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo e(($stats->rating_1 ?? 0) + ($stats->rating_2 ?? 0)); ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Rating Distribution -->
<div class="bg-white rounded-lg shadow mb-6 p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Rating Distribution</h3>
    <div class="space-y-3">
        <?php for($i = 5; $i >= 1; $i--): ?>
            <?php
                $count = $stats->{"rating_$i"} ?? 0;
                $total = $stats->total_feedbacks ?? 1;
                $percentage = $total > 0 ? ($count / $total) * 100 : 0;
            ?>
            <div class="flex items-center">
                <div class="w-12 text-sm text-gray-600"><?php echo e($i); ?> star</div>
                <div class="flex-1 mx-4">
                    <div class="h-6 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-yellow-400 transition-all" style="width: <?php echo e($percentage); ?>%"></div>
                    </div>
                </div>
                <div class="w-16 text-sm text-gray-600 text-right"><?php echo e($count); ?> (<?php echo e(number_format($percentage, 1)); ?>%)</div>
            </div>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>

<!-- Feedbacks List -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">All Feedbacks</h3>
    </div>

    <?php if(count($feedbacks) > 0): ?>
        <div class="divide-y divide-gray-200">
            <?php $__currentLoopData = $feedbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feedback): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <div class="font-medium text-gray-900">
                                    <?php echo e($feedback->user->full_name ?? $feedback->user->name ?? 'Anonymous'); ?>

                                </div>
                                <div class="ml-3 flex items-center">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($i <= ($feedback->rating ?? 0)): ?>
                                            <i class="fas fa-star text-yellow-400 text-sm"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-gray-300 text-sm"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <span class="ml-2 text-sm font-semibold text-gray-700"><?php echo e($feedback->rating ?? 0); ?>/5</span>
                                </div>
                            </div>
                            <?php if($feedback->comment): ?>
                                <p class="text-gray-700 mt-2 leading-relaxed"><?php echo e($feedback->comment); ?></p>
                            <?php else: ?>
                                <p class="text-gray-400 italic mt-2">No comment provided</p>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4 text-sm text-gray-500 text-right">
                            <?php echo e(isset($feedback->created_at) ? \Carbon\Carbon::parse($feedback->created_at)->format('M d, Y') : ''); ?><br>
                            <?php echo e(isset($feedback->created_at) ? \Carbon\Carbon::parse($feedback->created_at)->format('h:i A') : ''); ?>

                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12 text-gray-500">
            <i class="fas fa-comments text-4xl mb-4 text-gray-400"></i>
            <div class="text-lg font-medium">No feedbacks yet</div>
            <p class="mt-2">Participants can submit feedback after attending this event.</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Study\Kuliah\Semester-7\CP\event-connect\resources\views/admin/events/feedbacks.blade.php ENDPATH**/ ?>