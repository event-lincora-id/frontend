@extends('admin.layout')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-description', 'Manage all system notifications')

@section('content')
<div class="container-fluid">
    <!-- Header Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">All Notifications</h5>
                            <p class="text-muted mb-0">Total: {{ $notifications->count() }} notifications</p>
                        </div>
                        <div class="btn-group">
                            @if($notifications->where('is_read', false)->count() > 0)
                            <button onclick="markAllAsRead()" class="btn btn-primary">
                                <i class="fas fa-check-double"></i> Mark All as Read
                            </button>
                            @endif
                            <button onclick="deleteAllRead()" class="btn btn-secondary">
                                <i class="fas fa-trash"></i> Delete Read
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button class="nav-link active" onclick="filterNotifications('all')">
                All <span class="badge bg-secondary">{{ $notifications->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" onclick="filterNotifications('unread')">
                Unread <span class="badge bg-primary">{{ $notifications->where('is_read', false)->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" onclick="filterNotifications('read')">
                Read <span class="badge bg-success">{{ $notifications->where('is_read', true)->count() }}</span>
            </button>
        </li>
    </ul>

    <!-- Notifications List -->
    <div id="notifications-container">
        @forelse($notifications as $notification)
        <div class="card mb-3 notification-item {{ isset($notification['is_read']) && $notification['is_read'] ? 'read' : 'unread' }}" 
             data-id="{{ $notification['id'] ?? '' }}" 
             data-read="{{ isset($notification['is_read']) && $notification['is_read'] ? 'true' : 'false' }}">
            <div class="card-body">
                <div class="d-flex">
                    <!-- Icon -->
                    <div class="flex-shrink-0 me-3">
                        @php
                            $type = $notification['type'] ?? 'default';
                            $iconClass = 'fa-bell';
                            $iconColor = 'text-secondary';

                            switch($type) {
                                case 'event_registration':
                                    $iconClass = 'fa-user-plus';
                                    $iconColor = 'text-success';
                                    break;
                                case 'event_reminder':
                                case 'event_reminder_manual':
                                    $iconClass = 'fa-bell';
                                    $iconColor = 'text-warning';
                                    break;
                                case 'payment_confirmation':
                                    $iconClass = 'fa-credit-card';
                                    $iconColor = 'text-success';
                                    break;
                                case 'event_cancelled':
                                    $iconClass = 'fa-times-circle';
                                    $iconColor = 'text-danger';
                                    break;
                                case 'registration_success':
                                    $iconClass = 'fa-check-circle';
                                    $iconColor = 'text-success';
                                    break;
                            }
                        @endphp
                        <div class="rounded-circle bg-light p-3">
                            <i class="fas {{ $iconClass }} {{ $iconColor }} fa-2x"></i>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">{{ $notification['title'] ?? 'Notification' }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    {{ isset($notification['created_at']) ? \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() : '' }}
                                </small>
                            </div>
                            @if(!isset($notification['is_read']) || !$notification['is_read'])
                            <span class="badge bg-primary">New</span>
                            @endif
                        </div>

                        <p class="mb-2">{{ $notification['message'] ?? '' }}</p>

                        <!-- Event info if exists -->
                        @if(isset($notification['event']))
                        <div class="alert alert-light mb-2">
                            <i class="fas fa-calendar-alt text-primary"></i>
                            <strong>{{ $notification['event']['title'] ?? '' }}</strong>
                            @if(isset($notification['event']['start_date']))
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i>
                                {{ \Carbon\Carbon::parse($notification['event']['start_date'])->format('d M Y, H:i') }}
                            </small>
                            @endif
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="btn-group btn-group-sm">
                            @if(!isset($notification['is_read']) || !$notification['is_read'])
                            <button onclick="markAsRead('{{ $notification['id'] ?? '' }}')" class="btn btn-outline-primary">
                                <i class="fas fa-check"></i> Mark as Read
                            </button>
                            @endif
                            
                            @if(isset($notification['event']['id']))
                            <a href="{{ route('admin.events.show', $notification['event']['id']) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-eye"></i> View Event
                            </a>
                            @endif

                            <button onclick="deleteNotification('{{ $notification['id'] ?? '' }}')" class="btn btn-outline-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                <h5>No Notifications</h5>
                <p class="text-muted">You don't have any notifications yet.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(isset($pagination) && $pagination)
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            @if($pagination['current_page'] > 1)
            <li class="page-item">
                <a class="page-link" href="?page={{ $pagination['current_page'] - 1 }}">Previous</a>
            </li>
            @endif
            
            <li class="page-item disabled">
                <span class="page-link">Page {{ $pagination['current_page'] }} of {{ $pagination['last_page'] }}</span>
            </li>
            
            @if($pagination['current_page'] < $pagination['last_page'])
            <li class="page-item">
                <a class="page-link" href="?page={{ $pagination['current_page'] + 1 }}">Next</a>
            </li>
            @endif
        </ul>
    </nav>
    @endif
</div>

@push('scripts')
<script>
function filterNotifications(type) {
    const items = document.querySelectorAll('.notification-item');
    const tabs = document.querySelectorAll('.nav-link');
    
    // Update active tab
    tabs.forEach(tab => {
        tab.classList.remove('active');
        if (tab.textContent.toLowerCase().includes(type)) {
            tab.classList.add('active');
        }
    });
    
    // Filter items
    items.forEach(item => {
        const isRead = item.dataset.read === 'true';
        
        switch(type) {
            case 'unread':
                item.style.display = !isRead ? 'block' : 'none';
                break;
            case 'read':
                item.style.display = isRead ? 'block' : 'none';
                break;
            default:
                item.style.display = 'block';
        }
    });
}

async function markAsRead(id) {
    try {
        const response = await fetch(`/admin/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to mark notification as read');
    }
}

async function markAllAsRead() {
    if (!confirm('Mark all notifications as read?')) return;
    
    try {
        const response = await fetch('/admin/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to mark all as read');
    }
}

async function deleteNotification(id) {
    if (!confirm('Delete this notification?')) return;
    
    try {
        const response = await fetch(`/admin/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to delete notification');
    }
}

async function deleteAllRead() {
    if (!confirm('Delete all read notifications?')) return;
    
    const readItems = document.querySelectorAll('.notification-item[data-read="true"]');
    
    for (const item of readItems) {
        const id = item.dataset.id;
        await deleteNotification(id);
    }
    
    location.reload();
}
</script>
@endpush
@endsection
