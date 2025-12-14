@extends('admin.super-layout')

@section('title', 'Platform Management Dashboard')
@section('page-title', 'Platform Dashboard')
@section('page-description', 'Monitor all event organizers and events across the Event Connect platform')

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

<!-- Main Stats Cards - With Platform Color -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
    <!-- Total Organizers -->
    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md border-l-4 border-blue-500 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-700">Total Organizers</p>
                <p class="text-2xl md:text-3xl font-bold text-blue-900 mt-1">{{ number_format($stats['total_organizers'] ?? 0) }}</p>
                <p class="text-xs text-blue-700 mt-2">Event creators on platform</p>
            </div>
            <div class="p-3 bg-blue-500 text-white rounded-full">
                <i class="fas fa-crown text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Events -->
    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-md border-l-4 border-purple-500 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-700">Total Events</p>
                <p class="text-2xl md:text-3xl font-bold text-purple-900 mt-1">{{ number_format($stats['total_events'] ?? 0) }}</p>
                <p class="text-xs text-purple-700 mt-2">Across all organizers</p>
            </div>
            <div class="p-3 bg-purple-500 text-white rounded-full">
                <i class="fas fa-calendar-alt text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Participants -->
    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md border-l-4 border-green-500 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-700">Total Participants</p>
                <p class="text-2xl md:text-3xl font-bold text-green-900 mt-1">{{ number_format($stats['total_participants'] ?? 0) }}</p>
                <p class="text-xs text-green-700 mt-2">Registered users</p>
            </div>
            <div class="p-3 bg-green-500 text-white rounded-full">
                <i class="fas fa-users text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg shadow-md border-l-4 border-amber-500 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-700">Total Revenue</p>
                <p class="text-xl md:text-2xl font-bold text-amber-900 mt-1">Rp {{ number_format($stats['total_revenue'] ?? 0) }}</p>
                <p class="text-xs text-amber-700 mt-2">Platform earnings</p>
            </div>
            <div class="p-3 bg-amber-500 text-white rounded-full">
                <i class="fas fa-coins text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Event Status Breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
    <div class="bg-white rounded-lg shadow p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Event Status Distribution</h3>
        <div class="space-y-3">
            @php
                $statusColors = [
                    'published' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-l-4 border-green-500', 'icon' => 'check-circle'],
                    'draft' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'border' => 'border-l-4 border-yellow-500', 'icon' => 'edit'],
                    'completed' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-l-4 border-blue-500', 'icon' => 'check-double'],
                    'cancelled' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-l-4 border-red-500', 'icon' => 'times-circle'],
                ];
            @endphp
            @foreach($stats['event_breakdown'] ?? [] as $status => $count)
                @php $color = $statusColors[$status] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-l-4 border-gray-500', 'icon' => 'calendar']; @endphp
                <div class="flex items-center justify-between p-3 {{ $color['bg'] }} {{ $color['border'] }} rounded">
                    <div class="flex items-center">
                        <i class="fas fa-{{ $color['icon'] }} {{ $color['text'] }} mr-3"></i>
                        <span class="font-medium {{ $color['text'] }}">{{ ucfirst($status) }}</span>
                    </div>
                    <span class="font-bold text-lg {{ $color['text'] }}">{{ number_format($count) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Top Organizers -->
    <div class="bg-white rounded-lg shadow p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Top Organizers</h3>
        <div class="space-y-3">
            @forelse($stats['top_organizers'] ?? [] as $organizer)
            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr($organizer['name'] ?? 'O', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $organizer['name'] ?? 'Unknown' }}</p>
                        <p class="text-xs text-gray-500">{{ $organizer['email'] ?? '' }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-blue-600">{{ number_format($organizer['events_count'] ?? 0) }}</p>
                    <p class="text-xs text-gray-500">Events</p>
                </div>
            </div>
            @empty
            <div class="text-center py-6 text-gray-500">
                <i class="fas fa-inbox text-2xl mb-2"></i>
                <p>No organizers yet</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
    <!-- Monthly Trends -->
    <div class="bg-white rounded-lg shadow p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Monthly Trends (Events & Revenue)</h3>
        <canvas id="monthlyTrendsChart" width="400" height="200"></canvas>
    </div>

    <!-- Category Breakdown -->
    <div class="bg-white rounded-lg shadow p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Events by Category</h3>
        <canvas id="categoryChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
    <a href="{{ route('super.admin.organizers') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow border-t-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 font-medium">Manage Organizers</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['total_organizers'] ?? 0) }}</p>
            </div>
            <i class="fas fa-crown text-3xl text-blue-200"></i>
        </div>
    </a>

    <a href="{{ route('super.admin.events') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow border-t-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 font-medium">Monitor Events</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($stats['total_events'] ?? 0) }}</p>
            </div>
            <i class="fas fa-calendar-alt text-3xl text-purple-200"></i>
        </div>
    </a>

    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 font-medium">Total Revenue</p>
                <p class="text-xl font-bold text-green-600 mt-1">Rp {{ number_format(floor(($stats['total_revenue'] ?? 0) / 1000000)) }}M</p>
            </div>
            <i class="fas fa-coins text-3xl text-green-200"></i>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Monthly Trends Chart
    @php
        $months = [];
        $eventCounts = [];
        $revenues = [];
        foreach ($stats['monthly_trends'] ?? [] as $trend) {
            $months[] = $trend['month'] ?? '';
            $eventCounts[] = $trend['count'] ?? 0;
            $revenues[] = ($trend['total_amount'] ?? 0) / 1000000; // Convert to millions
        }
    @endphp

    const trendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'Events Created',
                data: {!! json_encode($eventCounts) !!},
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                yAxisID: 'y',
                pointRadius: 5,
                pointHoverRadius: 7,
            }, {
                label: 'Revenue (Millions Rp)',
                data: {!! json_encode($revenues) !!},
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4,
                yAxisID: 'y1',
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Events'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenue'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });

    // Category Breakdown Chart
    @php
        $categoryNames = [];
        $categoryCounts = [];
        $categoryColors = ['#3B82F6', '#8B5CF6', '#EC4899', '#F59E0B', '#10B981', '#06B6D4', '#EF4444'];
        foreach ($stats['category_breakdown'] ?? [] as $index => $category) {
            $categoryNames[] = $category['name'] ?? '';
            $categoryCounts[] = $category['count'] ?? 0;
        }
    @endphp

    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($categoryNames) !!},
            datasets: [{
                data: {!! json_encode($categoryCounts) !!},
                backgroundColor: {!! json_encode(array_slice($categoryColors, 0, count($categoryNames))) !!},
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endsection
