<?php $__env->startSection('title', 'Notifikasi - Event Connect'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Notifikasi</h1>
                <p class="mt-1 text-sm text-gray-600">Kelola semua notifikasi Anda</p>
            </div>
            <div class="flex space-x-2">
                <?php if($notifications->where('is_read', false)->count() > 0): ?>
                <button onclick="markAllAsRead()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-check-double mr-2"></i>Tandai Semua Dibaca
                </button>
                <?php endif; ?>
                <button onclick="deleteAllRead()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-trash mr-2"></i>Hapus Yang Dibaca
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button onclick="filterNotifications('all')" class="filter-tab active px-6 py-3 text-sm font-medium border-b-2 border-red-600 text-red-600">
                    Semua
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-red-100 text-red-600"><?php echo e($notifications->count()); ?></span>
                </button>
                <button onclick="filterNotifications('unread')" class="filter-tab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Belum Dibaca
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600"><?php echo e($notifications->where('is_read', false)->count()); ?></span>
                </button>
                <button onclick="filterNotifications('read')" class="filter-tab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Sudah Dibaca
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600"><?php echo e($notifications->where('is_read', true)->count()); ?></span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="space-y-4" id="notifications-container">
        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="notification-item bg-white rounded-lg shadow-sm hover:shadow-md transition <?php echo e(isset($notification['is_read']) && $notification['is_read'] ? 'opacity-75' : ''); ?>" 
             data-id="<?php echo e($notification['id'] ?? ''); ?>" 
             data-read="<?php echo e(isset($notification['is_read']) && $notification['is_read'] ? 'true' : 'false'); ?>">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <!-- Icon based on type -->
                            <div class="mr-3">
                                <?php
                                    $type = $notification['type'] ?? 'default';
                                ?>
                                <?php switch($type):
                                    case ('attendance_reminder'): ?>
                                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-clock text-yellow-600"></i>
                                        </div>
                                        <?php break; ?>
                                    <?php case ('payment_confirmation'): ?>
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-credit-card text-green-600"></i>
                                        </div>
                                        <?php break; ?>
                                    <?php case ('bookmark_event'): ?>
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-bookmark text-blue-600"></i>
                                        </div>
                                        <?php break; ?>
                                    <?php case ('event_update'): ?>
                                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-info-circle text-purple-600"></i>
                                        </div>
                                        <?php break; ?>
                                    <?php case ('event_cancelled'): ?>
                                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-times-circle text-red-600"></i>
                                        </div>
                                        <?php break; ?>
                                    <?php case ('registration_success'): ?>
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check-circle text-green-600"></i>
                                        </div>
                                        <?php break; ?>
                                    <?php case ('event_reminder'): ?>
                                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-bell text-orange-600"></i>
                                        </div>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-bell text-gray-600"></i>
                                        </div>
                                <?php endswitch; ?>
                            </div>

                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo e($notification['title'] ?? 'Notifikasi'); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo e($notification['created_at'] ?? now()); ?></p>
                            </div>

                            <!-- Unread indicator -->
                            <?php if(!isset($notification['is_read']) || !$notification['is_read']): ?>
                            <div class="ml-2">
                                <span class="inline-block w-3 h-3 bg-red-600 rounded-full"></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Message -->
                        <p class="text-gray-700 mb-3"><?php echo e($notification['message'] ?? ''); ?></p>

                        <!-- Event info if exists -->
                        <?php if(isset($notification['event'])): ?>
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                <span class="text-sm font-medium text-gray-900"><?php echo e($notification['event']['title'] ?? ''); ?></span>
                            </div>
                            <?php if(isset($notification['event']['start_date'])): ?>
                            <div class="flex items-center mt-1">
                                <i class="fas fa-clock text-gray-400 mr-2"></i>
                                <span class="text-sm text-gray-600"><?php echo e(\Carbon\Carbon::parse($notification['event']['start_date'])->format('d M Y, H:i')); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Action buttons -->
                        <div class="flex space-x-2">
                            <?php if(!isset($notification['is_read']) || !$notification['is_read']): ?>
                            <button onclick="markAsRead('<?php echo e($notification['id'] ?? ''); ?>')" class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition">
                                <i class="fas fa-check mr-1"></i>Tandai Dibaca
                            </button>
                            <?php endif; ?>
                            
                            <?php if(isset($notification['event']['id'])): ?>
                            <a href="<?php echo e(route('events.show', $notification['event']['id'])); ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
                                <i class="fas fa-eye mr-1"></i>Lihat Event
                            </a>
                            <?php endif; ?>

                            <button onclick="deleteNotification('<?php echo e($notification['id'] ?? ''); ?>')" class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200 transition">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-bell-slash text-gray-400 text-5xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak Ada Notifikasi</h3>
            <p class="text-gray-600">Anda belum memiliki notifikasi apapun.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if(isset($pagination) && $pagination): ?>
    <div class="mt-6">
        <div class="flex justify-center">
            <nav class="flex items-center space-x-2">
                <?php if($pagination['current_page'] > 1): ?>
                    <a href="?page=<?php echo e($pagination['current_page'] - 1); ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-chevron-left mr-2"></i>Previous
                    </a>
                <?php endif; ?>
                
                <span class="px-4 py-2 text-gray-700">
                    Page <?php echo e($pagination['current_page']); ?> of <?php echo e($pagination['last_page']); ?>

                </span>
                
                <?php if($pagination['current_page'] < $pagination['last_page']): ?>
                    <a href="?page=<?php echo e($pagination['current_page'] + 1); ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Next<i class="fas fa-chevron-right ml-2"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Rest of the JavaScript code remains the same -->
<script>
function filterNotifications(type) {
    const items = document.querySelectorAll('.notification-item');
    const tabs = document.querySelectorAll('.filter-tab');
    
    // Update active tab
    tabs.forEach(tab => {
        tab.classList.remove('active', 'border-red-600', 'text-red-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    event.target.classList.add('active', 'border-red-600', 'text-red-600');
    event.target.classList.remove('border-transparent', 'text-gray-500');
    
    // Filter items
    items.forEach(item => {
        const isRead = item.dataset.read === 'true';
        
        if (type === 'all') {
            item.style.display = 'block';
        } else if (type === 'unread') {
            item.style.display = !isRead ? 'block' : 'none';
        } else if (type === 'read') {
            item.style.display = isRead ? 'block' : 'none';
        }
    });
}

async function markAsRead(notificationId) {
    try {
        const response = await fetch(`/participant/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal menandai notifikasi');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menandai notifikasi');
    }
}

async function markAllAsRead() {
    if (!confirm('Tandai semua notifikasi sebagai dibaca?')) return;
    
    try {
        const response = await fetch('/participant/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal menandai semua notifikasi');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menandai semua notifikasi');
    }
}

async function deleteNotification(notificationId) {
    if (!confirm('Hapus notifikasi ini?')) return;
    
    try {
        const response = await fetch(`/participant/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal menghapus notifikasi');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus notifikasi');
    }
}

function deleteAllRead() {
    alert('Fitur ini akan segera tersedia');
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('participant.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Study\Kuliah\Semester-7\CP\event-connect\resources\views/participant/notifications/index.blade.php ENDPATH**/ ?>