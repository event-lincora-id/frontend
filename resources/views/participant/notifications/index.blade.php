@extends('participant.layout')

@section('title', 'Notifikasi - Event Connect')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Notifikasi</h1>
                <p class="mt-1 text-sm text-gray-600">Kelola semua notifikasi Anda</p>
            </div>
            <div class="flex space-x-2">
                @if($notifications->where('is_read', false)->count() > 0)
                <button onclick="markAllAsRead()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-check-double mr-2"></i>Tandai Semua Dibaca
                </button>
                @endif
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
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-red-100 text-red-600">{{ $notifications->total() }}</span>
                </button>
                <button onclick="filterNotifications('unread')" class="filter-tab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Belum Dibaca
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">{{ $notifications->where('is_read', false)->count() }}</span>
                </button>
                <button onclick="filterNotifications('read')" class="filter-tab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Sudah Dibaca
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">{{ $notifications->where('is_read', true)->count() }}</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="space-y-4" id="notifications-container">
        @forelse($notifications as $notification)
        <div class="notification-item bg-white rounded-lg shadow-sm hover:shadow-md transition {{ $notification->is_read ? 'opacity-75' : '' }}" 
             data-id="{{ $notification->id }}" 
             data-read="{{ $notification->is_read ? 'true' : 'false' }}">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <!-- Icon based on type -->
                            <div class="mr-3">
                                @switch($notification->type)
                                    @case('attendance_reminder')
                                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-clock text-yellow-600"></i>
                                        </div>
                                        @break
                                    @case('payment_confirmation')
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-credit-card text-green-600"></i>
                                        </div>
                                        @break
                                    @case('bookmark_event')
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-bookmark text-blue-600"></i>
                                        </div>
                                        @break
                                    @case('event_update')
                                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-info-circle text-purple-600"></i>
                                        </div>
                                        @break
                                    @case('event_cancelled')
                                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-times-circle text-red-600"></i>
                                        </div>
                                        @break
                                    @case('registration_success')
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check-circle text-green-600"></i>
                                        </div>
                                        @break
                                    @case('event_reminder')
                                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-bell text-orange-600"></i>
                                        </div>
                                        @break
                                    @default
                                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-bell text-gray-600"></i>
                                        </div>
                                @endswitch
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $notification->title }}</h3>
                                    @if(!$notification->is_read)
                                    <span class="ml-2 w-2 h-2 bg-red-600 rounded-full"></span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-gray-600">{{ $notification->message }}</p>
                                
                                <!-- Event Link (if exists) -->
                                @if($notification->event_id && $notification->event)
                                <a href="{{ route('events.show', $notification->event_id) }}" class="mt-2 inline-flex items-center text-sm text-red-600 hover:text-red-700">
                                    <i class="fas fa-external-link-alt mr-1"></i>
                                    Lihat Event: {{ $notification->event->title }}
                                </a>
                                @endif
                                
                                <p class="mt-2 text-xs text-gray-400">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="ml-4 flex items-center space-x-2">
                        @if(!$notification->is_read)
                        <button onclick="markAsRead({{ $notification->id }})" 
                                class="p-2 text-gray-400 hover:text-blue-600 transition" 
                                title="Tandai dibaca">
                            <i class="fas fa-check"></i>
                        </button>
                        @endif
                        <button onclick="deleteNotification({{ $notification->id }})" 
                                class="p-2 text-gray-400 hover:text-red-600 transition" 
                                title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-bell-slash text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak Ada Notifikasi</h3>
            <p class="text-gray-600">Anda belum memiliki notifikasi saat ini.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

<!-- JavaScript -->
<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Filter notifications
    function filterNotifications(type) {
        const items = document.querySelectorAll('.notification-item');
        const tabs = document.querySelectorAll('.filter-tab');
        
        // Update active tab
        tabs.forEach(tab => {
            tab.classList.remove('active', 'border-red-600', 'text-red-600');
            tab.classList.add('border-transparent', 'text-gray-500');
        });
        event.target.closest('.filter-tab').classList.add('active', 'border-red-600', 'text-red-600');
        event.target.closest('.filter-tab').classList.remove('border-transparent', 'text-gray-500');
        
        // Filter items
        items.forEach(item => {
            const isRead = item.dataset.read === 'true';
            
            if (type === 'all') {
                item.style.display = 'block';
            } else if (type === 'unread') {
                item.style.display = isRead ? 'none' : 'block';
            } else if (type === 'read') {
                item.style.display = isRead ? 'block' : 'none';
            }
        });
    }
    
    // Mark as read
    async function markAsRead(id) {
        try {
            const response = await fetch(`/participant/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const item = document.querySelector(`[data-id="${id}"]`);
                item.classList.add('opacity-75');
                item.dataset.read = 'true';
                item.querySelector('.w-2.h-2')?.remove(); // Remove red dot
                item.querySelector('button[onclick*="markAsRead"]')?.remove(); // Remove mark as read button
                
                // Update counts
                updateNotificationCounts();
                
                showToast('Notifikasi ditandai sebagai dibaca', 'success');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Gagal menandai notifikasi', 'error');
        }
    }
    
    // Mark all as read
    async function markAllAsRead() {
        if (!confirm('Tandai semua notifikasi sebagai dibaca?')) return;
        
        try {
            const response = await fetch('/participant/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Gagal menandai semua notifikasi', 'error');
        }
    }
    
    // Delete notification
    async function deleteNotification(id) {
        if (!confirm('Hapus notifikasi ini?')) return;
        
        try {
            const response = await fetch(`/participant/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const item = document.querySelector(`[data-id="${id}"]`);
                item.style.transition = 'opacity 0.3s';
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
                
                updateNotificationCounts();
                showToast('Notifikasi berhasil dihapus', 'success');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Gagal menghapus notifikasi', 'error');
        }
    }
    
    // Delete all read notifications
    async function deleteAllRead() {
        const readCount = document.querySelectorAll('[data-read="true"]').length;
        if (readCount === 0) {
            showToast('Tidak ada notifikasi yang sudah dibaca', 'info');
            return;
        }
        
        if (!confirm(`Hapus ${readCount} notifikasi yang sudah dibaca?`)) return;
        
        // For now, delete one by one (you can add bulk delete endpoint later)
        const readItems = document.querySelectorAll('[data-read="true"]');
        for (const item of readItems) {
            const id = item.dataset.id;
            await fetch(`/participant/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
        }
        
        location.reload();
    }
    
    // Update notification counts
    function updateNotificationCounts() {
        const allCount = document.querySelectorAll('.notification-item').length;
        const unreadCount = document.querySelectorAll('[data-read="false"]').length;
        const readCount = document.querySelectorAll('[data-read="true"]').length;
        
        // Update badge counts (you can implement this)
    }
    
    // Toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white shadow-lg z-50 ${
            type === 'success' ? 'bg-green-600' : 
            type === 'error' ? 'bg-red-600' : 
            'bg-blue-600'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
@endsection