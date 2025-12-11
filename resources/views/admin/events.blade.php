@extends('admin.layout')

@section('title', 'My Events')
@section('page-title', 'My Events')
@section('page-description', 'Manage your created events')

@section('content')
<!-- Action Bar -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">My Events</h2>
        <p class="text-gray-600">Manage your created events</p>
    </div>
    <a href="{{ route('admin.events.create') }}" style="background-color: var(--color-primary);" class="text-white px-6 py-3 rounded-lg hover:opacity-90 font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center text-lg">
        <i class="fas fa-plus mr-2"></i>Create New Event
    </a>
</div>

@if(isset($error))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ $error }}
    </div>
@endif

@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">My Events</h3>
            <a href="{{ route('admin.events.create') }}" style="background-color: var(--color-primary);" class="text-white px-4 py-2 rounded-lg hover:opacity-90 font-medium">
                <i class="fas fa-plus mr-2"></i>Create New Event
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="px-6 py-4 border-b border-gray-200">
        <form method="GET" action="{{ route('admin.events.index') }}">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search events..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="flex gap-2">
                    <select name="category_id" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <button type="submit" class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participants</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($events as $event)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                @if(isset($event->image) && $event->image)
                                    <img class="h-12 w-12 rounded-lg object-cover" src="{{ $event->image }}" alt="{{ $event->title }}">
                                @else
                                    <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $event->title ?? 'Untitled' }}</div>
                                <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($event->description ?? '', 40) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if(isset($event->category))
                            <span class="px-2 py-1 text-xs rounded-full" style="background-color: {{ $event->category->color ?? '#E5E7EB' }}20; color: {{ $event->category->color ?? '#6B7280' }}">
                                {{ is_object($event->category) ? ($event->category->name ?? 'Uncategorized') : ($event->category['name'] ?? 'Uncategorized') }}
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">Uncategorized</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if(isset($event->start_date))
                            <div>{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($event->start_date)->format('h:i A') }}</div>
                        @else
                            <div class="text-gray-400">No date</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="flex items-center">
                            <i class="fas fa-users text-gray-400 mr-2"></i>
                            {{ $event->participants_count ?? 0 }} / {{ $event->quota ?? '∞' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $eventStatus = $event->status ?? 'draft';
                            $statusConfig = [
                                'draft' => ['class' => 'bg-gray-100 text-gray-800', 'icon' => 'fa-file'],
                                'published' => ['class' => 'bg-green-100 text-green-800', 'icon' => 'fa-check-circle'],
                                'completed' => ['class' => 'bg-blue-100 text-blue-800', 'icon' => 'fa-flag-checkered'],
                                'cancelled' => ['class' => 'bg-red-100 text-red-800', 'icon' => 'fa-times-circle'],
                            ];
                            $status = $statusConfig[$eventStatus] ?? $statusConfig['draft'];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $status['class'] }}">
                            <i class="fas {{ $status['icon'] }} mr-1"></i>
                            {{ ucfirst($eventStatus) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.events.show', $event->id) }}" class="text-blue-600 hover:text-blue-800" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.events.edit', $event->id) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('admin.events.participants', $event->id) }}" class="text-green-600 hover:text-green-800" title="Participants">
                                <i class="fas fa-users"></i>
                            </a>
                            <button onclick="confirmDelete({{ $event->id }})" class="text-red-600 hover:text-red-800" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="text-gray-400">
                            <i class="fas fa-calendar-alt text-5xl mb-4"></i>
                            <div class="text-lg font-medium text-gray-900 mb-2">No events found</div>
                            <p class="text-gray-500 mb-4">Create your first event to get started!</p>
                            <a href="{{ route('admin.events.create') }}" style="background-color: var(--color-primary);" class="inline-block text-white px-6 py-2 rounded-lg hover:opacity-90">
                                <i class="fas fa-plus mr-2"></i>Create Event
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($events->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $events->links() }}
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Event</h3>
                    <p class="text-sm text-gray-500">Are you sure you want to delete this event? This action cannot be undone and all participant data will be lost.</p>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end rounded-b-lg">
            <button onclick="closeDeleteModal()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
            </button>
            <form id="deleteForm" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>Delete Event
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(eventId) {
    document.getElementById('deleteForm').action = '{{ route("admin.events.index") }}/' + eventId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
@endsection