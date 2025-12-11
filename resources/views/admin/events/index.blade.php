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

<div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">My Events</h3>
            <a href="{{ route('admin.events.create') }}" style="background-color: var(--color-primary);" class="text-white px-6 py-3 rounded-lg hover:opacity-90 font-medium shadow-md hover:shadow-lg transition-all duration-200 flex items-center">
                <i class="fas fa-plus mr-2"></i>Create New Event
            </a>
        </div>
    </div>

    @if(isset($error))
        <div class="p-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $error }}
            </div>
        </div>
    @endif

    <!-- Search and Filter -->
    <div class="px-6 py-4 border-b border-gray-200">
        <form method="GET" action="{{ route('admin.events.index') }}">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search events..." class="admin-input w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="flex gap-2">
                    <select name="category_id" class="admin-input px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status" class="admin-input px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
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
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-16 w-16">
                                @if(isset($event->image) && $event->image)
                                    <img class="h-16 w-16 rounded-lg object-cover" src="{{ $event->image }}" alt="{{ $event->title }}">
                                @else
                                    <div class="h-16 w-16 rounded-lg bg-gray-300 flex items-center justify-center">
                                        <i class="fas fa-calendar text-gray-600"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $event->title ?? 'Untitled' }}</div>
                                <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($event->description ?? '', 50) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if(isset($event->category))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ is_object($event->category) ? ($event->category->name ?? 'Uncategorized') : ($event->category['name'] ?? 'Uncategorized') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Uncategorized
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if(isset($event->start_date))
                            <div>{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }}</div>
                            <div class="text-gray-500">{{ \Carbon\Carbon::parse($event->start_date)->format('h:i A') }}</div>
                        @else
                            <div class="text-gray-500">No date</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $event->participants_count ?? 0 }} / {{ $event->quota ?? '∞' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $eventStatus = $event->status ?? 'draft';
                            $statusClasses = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'published' => 'bg-green-100 text-green-800',
                                'completed' => 'bg-blue-100 text-blue-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ];
                            $statusClass = $statusClasses[$eventStatus] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                            {{ ucfirst($eventStatus) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.events.show', $event->id) }}" style="color: var(--color-primary);" class="hover:opacity-80" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.events.edit', $event->id) }}" class="text-gray-600 hover:text-gray-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('admin.events.participants', $event->id) }}" class="text-green-600 hover:text-green-900" title="Participants">
                                <i class="fas fa-users"></i>
                            </a>
                            <button class="text-red-600 hover:text-red-900" onclick="confirmDelete({{ $event->id }})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-calendar-alt text-4xl mb-4 text-gray-400"></i>
                        <div class="text-lg font-medium">No events found</div>
                        <p class="mt-2">Create your first event to get started!</p>
                        <a href="{{ route('admin.events.create') }}" style="background-color: var(--color-primary);" class="inline-block mt-4 text-white px-6 py-2 rounded-lg hover:opacity-90">
                            <i class="fas fa-plus mr-2"></i>Create Event
                        </a>
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
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Event</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete this event? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
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
@endsection

@section('scripts')
<script>
function confirmDelete(eventId) {
    document.getElementById('deleteForm').action = '/admin/events/' + eventId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endsection