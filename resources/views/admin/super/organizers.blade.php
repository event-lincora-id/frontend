@extends('admin.super-layout')

@section('title', 'Event Organizers Management')
@section('page-title', 'Event Organizers')
@section('page-description', 'Manage all event organizers on the Event Connect platform')

@section('content')

<!-- Error Message -->
@if(isset($error))
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
        <p class="text-red-800 font-semibold">{{ $error }}</p>
    </div>
</div>
@endif

<!-- Search and Filter Section -->
<div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6 md:mb-8">
    <form method="GET" action="{{ route('super.admin.organizers') }}" class="flex flex-col md:flex-row md:items-end gap-4">
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Organizers</label>
            <div class="relative">
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    placeholder="Search by name or email..."
                    value="{{ request('search') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" id="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                <i class="fas fa-search"></i>
                Search
            </button>
            @if(request('search') || request('status'))
            <a href="{{ route('super.admin.organizers') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                Clear
            </a>
            @endif
        </div>
    </form>

    @if(request('search') || request('status'))
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
        <i class="fas fa-info-circle mr-2"></i>
        Showing organizers {{ request('search') ? "matching '" . request('search') . "'" : '' }}{{ request('status') && request('search') ? ' and ' : '' }}{{ request('status') ? "with status '" . request('status') . "'" : '' }}
    </div>
    @endif
</div>

<!-- Results Counter -->
<div class="mb-4 text-sm text-gray-600">
    @if($organizers->count() > 0)
        <span class="font-semibold text-gray-800">Showing {{ $organizers->firstItem() }} to {{ $organizers->lastItem() }} of {{ $organizers->total() }} organizers</span>
    @else
        @if(request('search') || request('status'))
            <span class="text-gray-500">No organizers found matching your criteria</span>
        @else
            <span class="text-gray-500">No organizers yet</span>
        @endif
    @endif
</div>

<!-- Organizers Grid -->
@if($organizers->count() > 0)
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
    @foreach($organizers as $organizer)
    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-blue-500 p-4 md:p-6">
        <!-- Header with Status -->
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                    {{ strtoupper(substr($organizer->name ?? 'O', 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $organizer->name ?? 'Unknown' }}</h3>
                    <p class="text-xs text-gray-500">{{ $organizer->email ?? '' }}</p>
                </div>
            </div>
            
            @php
                $statusColor = match($organizer->status ?? 'active') {
                    'active' => 'bg-green-100 text-green-800',
                    'inactive' => 'bg-yellow-100 text-yellow-800',
                    'suspended' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                $statusIcon = match($organizer->status ?? 'active') {
                    'active' => 'check-circle',
                    'inactive' => 'clock',
                    'suspended' => 'ban',
                    default => 'question-circle'
                };
            @endphp
            <span class="px-2 py-1 {{ $statusColor }} rounded text-xs font-medium flex items-center gap-1">
                <i class="fas fa-{{ $statusIcon }}"></i>
                {{ ucfirst($organizer->status ?? 'unknown') }}
            </span>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-blue-50 rounded p-3 border-l-2 border-blue-400">
                <p class="text-2xl font-bold text-blue-600">{{ $organizer->events->total ?? 0 }}</p>
                <p class="text-xs text-gray-600">Total Events</p>
            </div>
            <div class="bg-green-50 rounded p-3 border-l-2 border-green-400">
                <p class="text-2xl font-bold text-green-600">{{ number_format($organizer->events->total_participants ?? 0) }}</p>
                <p class="text-xs text-gray-600">Participants</p>
            </div>
            <div class="bg-purple-50 rounded p-3 border-l-2 border-purple-400">
                <p class="text-2xl font-bold text-purple-600">{{ number_format($organizer->events->published ?? 0) }}</p>
                <p class="text-xs text-gray-600">Published</p>
            </div>
            <div class="bg-amber-50 rounded p-3 border-l-2 border-amber-400">
                <p class="text-2xl font-bold text-amber-600">Rp {{ number_format($organizer->events->total_revenue ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-600">Revenue</p>
            </div>
        </div>

        <!-- Event Breakdown -->
        @php
            $breakdown = [
                'draft' => $organizer->events->draft ?? 0,
                'published' => $organizer->events->published ?? 0,
                'completed' => $organizer->events->completed ?? 0,
                'cancelled' => $organizer->events->cancelled ?? 0,
            ];
            $breakdown = array_filter($breakdown, fn($count) => $count > 0);
        @endphp
        @if(!empty($breakdown))
        <div class="bg-gray-50 rounded p-3 mb-4 text-xs space-y-1">
            <p class="font-medium text-gray-700 mb-2">Event Status</p>
            @foreach($breakdown as $status => $count)
            <div class="flex justify-between">
                <span class="text-gray-600 capitalize">{{ $status }}:</span>
                <span class="font-semibold text-gray-800">{{ $count }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col gap-2 pt-4 border-t border-gray-200">
            <div class="flex gap-2">
                <a href="{{ route('super.admin.organizer.detail', $organizer->id) }}" class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition-colors text-sm font-medium text-center">
                    <i class="fas fa-eye mr-1"></i> View
                </a>
                @if($organizer->status === 'suspended')
                <button type="button" onclick="activateOrganizer({{ $organizer->id }})" class="flex-1 px-3 py-2 bg-green-50 text-green-600 rounded hover:bg-green-100 transition-colors text-sm font-medium">
                    <i class="fas fa-check-circle mr-1"></i> Activate
                </button>
                @else
                <button type="button" onclick="suspendOrganizer({{ $organizer->id }})" class="flex-1 px-3 py-2 bg-yellow-50 text-yellow-600 rounded hover:bg-yellow-100 transition-colors text-sm font-medium">
                    <i class="fas fa-ban mr-1"></i> Suspend
                </button>
                @endif
            </div>
            <button type="button" onclick="confirmDeleteOrganizer({{ $organizer->id }}, '{{ addslashes($organizer->name ?? 'this organizer') }}')" class="w-full px-3 py-2 bg-red-50 text-red-600 rounded hover:bg-red-100 transition-colors text-sm font-medium">
                <i class="fas fa-trash mr-1"></i> Delete
            </button>
        </div>
    </div>
    @endforeach
</div>

<!-- Pagination -->
@if($organizers->hasPages())
<div class="flex justify-center items-center gap-2 mt-8">
    @if($organizers->onFirstPage())
        <button class="px-3 py-1 text-gray-400 cursor-not-allowed" disabled>Previous</button>
    @else
        <a href="{{ $organizers->previousPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">Previous</a>
    @endif

    @foreach($organizers->getUrlRange(1, $organizers->lastPage()) as $page => $url)
        @if($page == $organizers->currentPage())
            <button class="px-3 py-1 bg-blue-600 text-white rounded font-semibold" disabled>{{ $page }}</button>
        @else
            <a href="{{ $url }}" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">{{ $page }}</a>
        @endif
    @endforeach

    @if($organizers->hasMorePages())
        <a href="{{ $organizers->nextPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">Next</a>
    @else
        <button class="px-3 py-1 text-gray-400 cursor-not-allowed" disabled>Next</button>
    @endif
</div>
@endif

@else
<!-- Empty State -->
<div class="bg-white rounded-lg shadow p-8 text-center">
    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
    <h3 class="text-lg font-semibold text-gray-700 mb-2">No Organizers Found</h3>
    @if(request('search') || request('status'))
        <p class="text-gray-500 mb-4">No organizers match your search criteria</p>
        <a href="{{ route('super.admin.organizers') }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            Clear Filters
        </a>
    @else
        <p class="text-gray-500">There are no event organizers on the platform yet</p>
    @endif
</div>
@endif

<!-- Delete Confirmation Modal -->
<div id="deleteOrganizerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Organizer</h3>
            <p class="text-sm text-gray-500 mb-4">
                Are you sure you want to delete <span id="deleteOrganizerName" class="font-semibold text-gray-900"></span>?
                This action cannot be undone and will also affect all their events.
            </p>
            <div class="flex gap-3">
                <button onclick="closeDeleteOrganizerModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="deleteOrganizer()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let deleteOrganizerId = null;

// Get CSRF token from meta tag or cookie
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        return token.getAttribute('content');
    }
    // Fallback: try to get from cookie
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'XSRF-TOKEN') {
            return decodeURIComponent(value);
        }
    }
    return '';
}

function suspendOrganizer(organizerId) {
    if (!confirm('Are you sure you want to suspend this organizer? They will not be able to create or manage events.')) {
        return;
    }

    fetch(`/super-admin/users/${organizerId}/suspend`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while suspending the organizer');
    });
}

function activateOrganizer(organizerId) {
    if (!confirm('Are you sure you want to activate this organizer?')) {
        return;
    }

    fetch(`/super-admin/users/${organizerId}/activate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while activating the organizer');
    });
}

function confirmDeleteOrganizer(organizerId, organizerName) {
    deleteOrganizerId = organizerId;
    document.getElementById('deleteOrganizerName').textContent = organizerName;
    document.getElementById('deleteOrganizerModal').classList.remove('hidden');
}

function closeDeleteOrganizerModal() {
    deleteOrganizerId = null;
    document.getElementById('deleteOrganizerModal').classList.add('hidden');
}

function deleteOrganizer() {
    if (!deleteOrganizerId) return;

    fetch(`/super-admin/users/${deleteOrganizerId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeDeleteOrganizerModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the organizer');
    });
}

// Close modal when clicking outside
document.getElementById('deleteOrganizerModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteOrganizerModal();
    }
});
</script>
@endsection
