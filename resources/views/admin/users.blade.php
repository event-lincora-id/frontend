<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Event Connect Admin </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="flex h-screen">
        <div class="w-64 shadow-lg" style="background-color: #B22234;">
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Event Connect</h1>
                        <p class="text-white/80 text-xs">Admin Dashboard</p>
                    </div>
                </div>
            </div>
            <nav class="mt-6">
                <a href="/admin/dashboard" class="flex items-center px-6 py-3 text-white/80 hover:bg-white/10">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="/admin/users" class="flex items-center px-6 py-3 text-white bg-white/20 border-r-4 border-white">
                    <i class="fas fa-users mr-3"></i>
                    Users
                </a>
                <a href="/admin/events" class="flex items-center px-6 py-3 text-white/80 hover:bg-white/10">
                    <i class="fas fa-calendar-alt mr-3"></i>
                    Events
                </a>
                <a href="/admin/finance" class="flex items-center px-6 py-3 text-white/80 hover:bg-white/10">
                    <i class="fas fa-coins mr-3"></i>
                    Finance
                </a>
                <a href="/admin/categories" class="flex items-center px-6 py-3 text-white/80 hover:bg-white/10">
                    <i class="fas fa-tags mr-3"></i>
                    Categories
                </a>
                <a href="/admin/analytics" class="flex items-center px-6 py-3 text-white/80 hover:bg-white/10">
                    <i class="fas fa-chart-line mr-3"></i>
                    Analytics
                </a>
                <form action="{{ route('logout') }}" method="POST" class="mt-6 px-6">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-start px-0 py-3 text-left text-white/80 hover:bg-white/10 rounded">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4">
                    <h2 class="text-3xl font-bold text-gray-800">Users Management</h2>
                    <p class="text-gray-600">Manage all registered users and their activities.</p>
                </div>
            </header>

            <!-- Content -->
            <div class="p-6">
                <!-- Search and Filters -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" placeholder="Search users..." class="admin-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-transparent">
                        </div>
                        <div class="flex gap-2">
                            <select class="admin-input px-4 py-2 border border-gray-300 rounded-lg">
                                <option>All Users</option>
                                <option>Organizers</option>
                                <option>Regular Users</option>
                            </select>
                            <button style="background-color: #B22234;" class="px-4 py-2 text-white rounded-lg hover:opacity-90">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events & Participation</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($user->avatar)
                                                    <img class="h-10 w-10 rounded-full" src="{{ $user->avatar }}" alt="">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-600"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                                <div class="text-sm text-gray-500">ID: {{ $user->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                        @if($user->phone)
                                            <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(($user->role ?? null) === 'super_admin')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-star mr-1"></i>Super Admin
                                            </span>
                                        @elseif(($user->role ?? null) === 'admin')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-crown mr-1"></i>Organizer
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-user mr-1"></i>User
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-4">
                                                <div class="text-center">
                                                    <div class="font-medium">{{ $user->events_count ?? 0 }}</div>
                                                    <div class="text-xs text-gray-500">Created</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="font-medium">{{ $user->eventParticipants->count() }}</div>
                                                    <div class="text-xs text-gray-500">Joined</div>
                                                </div>
                                            </div>
                                            @if($user->eventParticipants->count() > 0)
                                                <div class="mt-2">
                                                    <div class="text-xs text-gray-500 mb-1">Events Joined:</div>
                                                    <div class="space-y-1 max-h-20 overflow-y-auto">
                                                        @foreach($user->eventParticipants->take(3) as $participation)
                                                            <div class="flex items-center justify-between bg-gray-50 px-2 py-1 rounded text-xs">
                                                                <span class="font-medium text-gray-700 truncate">{{ $participation->event->title ?? 'Event Deleted' }}</span>
                                                                <span class="ml-2 px-1 py-0.5 rounded text-xs
                                                                    @if($participation->status === 'attended') bg-green-100 text-green-800
                                                                    @elseif($participation->status === 'registered') bg-blue-100 text-blue-800
                                                                    @else bg-red-100 text-red-800
                                                                    @endif">
                                                                    {{ ucfirst($participation->status) }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                        @if($user->eventParticipants->count() > 3)
                                                            <div class="text-xs text-gray-400 text-center">
                                                                +{{ $user->eventParticipants->count() - 3 }} more events
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-400 italic">No events joined</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if($user->eventParticipants->count() > 0)
                                                <button onclick="toggleUserEvents({{ $user->id }})" class="text-purple-600 hover:text-purple-900" title="View Events">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                            @endif
                                            <button class="text-blue-600 hover:text-blue-900" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-green-600 hover:text-green-900" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900" title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Expandable row for user events -->
                                <tr id="user-events-{{ $user->id }}" class="hidden bg-gray-50">
                                    <td colspan="6" class="px-6 py-4">
                                        <div class="bg-white rounded-lg shadow-sm p-4">
                                            <h4 class="text-lg font-semibold text-gray-800 mb-4">
                                                <i class="fas fa-calendar-alt mr-2"></i>
                                                Events Joined by {{ $user->full_name }}
                                            </h4>
                                            @if($user->eventParticipants->count() > 0)
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    @foreach($user->eventParticipants as $participation)
                                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                                            <div class="flex justify-between items-start mb-2">
                                                                <h5 class="font-medium text-gray-900 truncate">{{ $participation->event->title ?? 'Event Deleted' }}</h5>
                                                                <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium
                                                                    @if($participation->status === 'attended') bg-green-100 text-green-800
                                                                    @elseif($participation->status === 'registered') bg-blue-100 text-blue-800
                                                                    @else bg-red-100 text-red-800
                                                                    @endif">
                                                                    {{ ucfirst($participation->status) }}
                                                                </span>
                                                            </div>
                                                            @if($participation->event)
                                                                <div class="space-y-1 text-sm text-gray-600">
                                                                    <div class="flex items-center">
                                                                        <i class="fas fa-calendar mr-2"></i>
                                                                        <span>{{ \Carbon\Carbon::parse($participation->event->start_date)->format('M d, Y H:i') }}</span>
                                                                    </div>
                                                                    <div class="flex items-center">
                                                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                                                        <span>{{ $participation->event->location }}</span>
                                                                    </div>
                                                                    <div class="flex items-center">
                                                                        <i class="fas fa-tag mr-2"></i>
                                                                        <span>{{ $participation->event->category->name ?? 'No Category' }}</span>
                                                                    </div>
                                                                    @if($participation->is_paid)
                                                                        <div class="flex items-center">
                                                                            <i class="fas fa-dollar-sign mr-2"></i>
                                                                            <span class="text-green-600 font-medium">Paid - ${{ number_format($participation->amount_paid ?? $participation->event->price, 2) }}</span>
                                                                        </div>
                                                                    @endif
                                                                    @if($participation->attended_at)
                                                                        <div class="flex items-center">
                                                                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                                                                            <span class="text-green-600">Attended on {{ \Carbon\Carbon::parse($participation->attended_at)->format('M d, Y H:i') }}</span>
                                                                        </div>
                                                                    @endif
                                                                    @if($participation->qr_code)
                                                                        <div class="flex items-center justify-between mt-2">
                                                                            <div class="flex items-center">
                                                                                <i class="fas fa-qrcode mr-2 text-blue-500"></i>
                                                                                <span class="text-sm text-blue-600">Personal QR Code</span>
                                                                            </div>
                                                                            <a href="{{ asset('storage/' . $participation->qr_code) }}" target="_blank" class="text-xs text-blue-500 hover:text-blue-700">
                                                                                <i class="fas fa-external-link-alt"></i> View
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="text-sm text-red-500 italic">Event has been deleted</div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-8 text-gray-500">
                                                    <i class="fas fa-calendar-times text-4xl mb-4"></i>
                                                    <p>This user hasn't joined any events yet.</p>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleUserEvents(userId) {
            const eventRow = document.getElementById(`user-events-${userId}`);
            const button = document.querySelector(`button[onclick="toggleUserEvents(${userId})"]`);
            
            if (eventRow.classList.contains('hidden')) {
                eventRow.classList.remove('hidden');
                button.innerHTML = '<i class="fas fa-chevron-up"></i>';
                button.title = 'Hide Events';
            } else {
                eventRow.classList.add('hidden');
                button.innerHTML = '<i class="fas fa-list"></i>';
                button.title = 'View Events';
            }
        }
    </script>
</body>
</html>
