@extends('admin.layout')

@section('title', 'Dashboard - Event Organizer')
@section('page-title', 'My Dashboard')
@section('page-description', 'Welcome back! Comprehensive overview of your events, revenue, and performance.')

@section('content')
<div class="p-0 md:p-6">
    @if(isset($error))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ $error }}
    </div>
    @endif

    <!-- Date Range Filter -->
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h2>
        <div class="flex items-center space-x-2">
            <label for="dateRange" class="text-sm text-gray-600">Date Range:</label>
            <select id="dateRange" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500" onchange="changeDateRange(this.value)">
                <option value="7" {{ $date_range == '7' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30" {{ $date_range == '30' ? 'selected' : '' }}>Last 30 Days</option>
                <option value="90" {{ $date_range == '90' ? 'selected' : '' }}>Last 90 Days</option>
            </select>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
        <!-- Total Events -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-calendar-alt text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Events</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_events'] ?? 0) }}</p>
                    <p class="text-sm text-gray-500">{{ $stats['active_events'] ?? 0 }} active, {{ $stats['completed_events'] ?? 0 }} completed</p>
                </div>
            </div>
        </div>

        <!-- Total Participants -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Participants</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_participants'] ?? 0) }}</p>
                    <p class="text-sm text-green-600">+{{ $stats['this_month_participants'] ?? 0 }} this month</p>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Earned</p>
                    <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($balance['total_earned'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">Rp {{ number_format($balance['available_balance'] ?? 0, 0, ',', '.') }} available</p>
                </div>
            </div>
        </div>

        <!-- Average Rating -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Average Rating</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($avg_rating ?? 0, 1) }}</p>
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star text-sm {{ $i <= ($avg_rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart & Payment Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Revenue Trends -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-line mr-2 text-gray-600"></i>Revenue Trends
            </h3>
            <div style="height: 300px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Payment Statistics -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-credit-card mr-2 text-gray-600"></i>Payment Statistics
            </h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Paid Registrations</span>
                        <span class="font-semibold">{{ $balance_statistics['paid_registrations'] ?? 0 }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Total Withdrawals</span>
                        <span class="font-semibold">{{ $balance_statistics['total_withdrawal_requests'] ?? 0 }}</span>
                    </div>
                    <div class="text-xs text-gray-500">{{ $balance_statistics['approved_withdrawals'] ?? 0 }} approved</div>
                </div>

                <div class="pt-4 border-t">
                    <p class="text-xs text-gray-600 mb-2">Platform Fee</p>
                    <p class="text-lg font-bold text-gray-900">{{ $balance_statistics['platform_fee_percentage'] ?? 5 }}%</p>
                </div>

                <div>
                    <p class="text-xs text-gray-600 mb-2">Min. Withdrawal</p>
                    <p class="text-lg font-bold text-gray-900">Rp {{ number_format($balance_statistics['minimum_withdrawal_amount'] ?? 50000, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Revenue Events -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-trophy mr-2 text-gray-600"></i>Top Revenue Events
            </h3>
            <a href="{{ route('admin.finance.index') }}" class="text-sm text-red-600 hover:text-red-700">
                View detailed transactions <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participants</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($top_events as $event)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $event['title'] ?? 'Untitled' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $event['participants_count'] ?? 0 }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-green-600">
                                Rp {{ number_format(($event['price'] ?? 0) * ($event['participants_count'] ?? 0), 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ isset($event['start_date']) ? \Carbon\Carbon::parse($event['start_date'])->format('d M Y') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 opacity-50"></i>
                            <p>No revenue data available</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity & Top Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Recent Activity Feed -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-history mr-2 text-gray-600"></i>Recent Activity
            </h3>
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @forelse($recent_activities as $activity)
                <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-{{ $activity['color'] ?? 'blue' }}-100 flex items-center justify-center">
                            <i class="fas fa-{{ $activity['icon'] ?? 'bell' }} text-{{ $activity['color'] ?? 'blue' }}-600"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900">{{ $activity['message'] ?? 'Activity' }}</p>
                        <p class="text-xs text-gray-500">{{ isset($activity['time']) ? \Carbon\Carbon::parse($activity['time'])->diffForHumans() : 'Recently' }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-bell-slash text-5xl mb-4 opacity-50"></i>
                    <p class="text-sm">No recent activities</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Top Events -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-trophy mr-2 text-gray-600"></i>Top Events by Participants
            </h3>
            <div class="space-y-4">
                @forelse($top_events as $event)
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $event['title'] ?? 'Untitled Event' }}</p>
                        <p class="text-xs text-gray-500">{{ isset($event['start_date']) ? \Carbon\Carbon::parse($event['start_date'])->format('d M Y') : 'Date TBD' }}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm font-medium text-gray-700">
                            <i class="fas fa-users text-gray-400 mr-1"></i>{{ $event['participants_count'] ?? 0 }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-calendar-times text-5xl mb-4 opacity-50"></i>
                    <p class="text-sm">No events yet</p>
                    <a href="{{ route('admin.events.create') }}" class="mt-4 inline-block text-sm text-red-600 hover:text-red-700">
                        <i class="fas fa-plus mr-1"></i>Create your first event
                    </a>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Date Range Change
function changeDateRange(value) {
    window.location.href = '{{ route("admin.dashboard") }}?date_range=' + value;
}

// Revenue Trends Chart
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    const revenueTrends = @json($revenue_analytics['revenue_trends'] ?? []);
    new Chart(revenueCtx.getContext('2d'), {
        type: 'line',
        data: {
            labels: revenueTrends.map(t => t.date || ''),
            datasets: [{
                label: 'Revenue',
                data: revenueTrends.map(t => t.revenue || 0),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
}
</script>
@endsection
