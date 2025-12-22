@extends('admin.super-layout')

@section('content')
<div class="p-4 md:p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Withdrawal Management</h2>
        <p class="text-gray-600">Review and manage withdrawal requests from all organizers</p>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <!-- Pending Requests -->
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border-l-4 border-yellow-500 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-yellow-700 mb-1">Pending Requests</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_count'] ?? 0 }}</p>
                    <p class="text-xs text-yellow-600 mt-1">
                        Rp {{ number_format($stats['pending_amount'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <i class="fas fa-clock text-3xl text-yellow-500"></i>
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-500 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-700 mb-1">Approved</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['approved_count'] ?? 0 }}</p>
                    <p class="text-xs text-green-600 mt-1">
                        Rp {{ number_format($stats['approved_amount'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <i class="fas fa-check-circle text-3xl text-green-500"></i>
            </div>
        </div>

        <!-- Rejected -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-500 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-red-700 mb-1">Rejected</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['rejected_count'] ?? 0 }}</p>
                    <p class="text-xs text-red-600 mt-1">
                        Rp {{ number_format($stats['rejected_amount'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <i class="fas fa-times-circle text-3xl text-red-500"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <select name="status" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From Date"
                   class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To Date"
                   class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" style="background-color: #B22234 !important;" class="text-white font-semibold px-6 py-2 rounded-md hover:opacity-90 transition-opacity shadow-md">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
        </form>
    </div>

    <!-- Withdrawal Requests Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if(count($withdrawals) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organizer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($withdrawals as $wd)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ isset($wd['requested_at']) ? \Carbon\Carbon::parse($wd['requested_at'])->format('M d, Y') : (isset($wd['created_at']) ? \Carbon\Carbon::parse($wd['created_at'])->format('M d, Y') : '-') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $wd['organizer']['name'] ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $wd['organizer']['email'] ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-green-600">
                            Rp {{ number_format($wd['amount'] ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $wd['bank_name'] ?? '-' }}<br>
                            <span class="text-xs text-gray-500">{{ $wd['bank_account_number'] ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(($wd['status'] ?? '') === 'pending')
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif(($wd['status'] ?? '') === 'approved')
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Approved
                                </span>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if(($wd['status'] ?? '') === 'pending')
                                <button onclick="reviewWithdrawal({{ $wd['id'] ?? 0 }})"
                                        class="bg-[#B22234] text-white px-3 py-1 rounded-md hover:opacity-90 mr-2">
                                    <i class="fas fa-eye mr-1"></i>Review
                                </button>
                            @else
                                <button onclick="viewWithdrawalDetails({{ $wd['id'] ?? 0 }})"
                                        class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye mr-1"></i>View
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center">
            <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-600">No withdrawal requests found</p>
            <p class="text-sm text-gray-500 mt-2">Withdrawal requests will appear here when organizers submit them</p>
        </div>
        @endif
    </div>
</div>

<!-- Review Withdrawal Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-screen overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold">Review Withdrawal Request</h3>
                <button onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div id="reviewContent">
                <!-- Content loaded via fetch -->
            </div>
        </div>
    </div>
</div>

<script>
async function reviewWithdrawal(id) {
    const modal = document.getElementById('reviewModal');
    const content = document.getElementById('reviewContent');

    // Show loading
    content.innerHTML = '<p class="text-center py-8"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</p>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // Fetch withdrawal details
    try {
        const response = await fetch(`/super-admin/withdrawals/${id}`);
        const html = await response.text();
        content.innerHTML = html;
    } catch (error) {
        content.innerHTML = '<p class="text-red-600 text-center py-8"><i class="fas fa-exclamation-circle mr-2"></i>Failed to load withdrawal details</p>';
    }
}

async function viewWithdrawalDetails(id) {
    reviewWithdrawal(id); // Same function, just view mode
}

function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
