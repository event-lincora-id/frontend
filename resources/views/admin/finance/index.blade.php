@extends('admin.layout')

@section('content')
<div class="p-4 md:p-6">
    <div class="px-6 py-4 border-b border-gray-200 bg-white">
        
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
            
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Finance & Withdrawals</h2>
                <p class="text-gray-600">Manage your earnings, revenue, and withdrawal requests</p>
            </div>

            <button onclick="openWithdrawalModal()" style="background-color: #B22234 !important;" 
            class="text-white font-semibold px-6 py-3 rounded-lg hover:opacity-90 transition-opacity shadow-md whitespace-nowrap">
                <i class="fas fa-money-check-alt mr-2"></i>Request Withdrawal
            </button>

        </div>
        <br>
            @if($balanceData)
            <!-- Balance Dashboard Section -->
            <div class="mb-2">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Balance Overview</h3>

                <!-- Balance Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Total Earned Card -->
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total Earned</p>
                                <p class="text-xl md:text-2xl font-semibold text-gray-900">
                                    Rp {{ number_format($balanceData['balance']['total_earned'] ?? 0, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full bg-green-100">
                                <i class="fas fa-wallet text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Available Balance Card -->
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Available Balance</p>
                                <p class="text-xl md:text-2xl font-semibold text-blue-600">
                                    Rp {{ number_format($balanceData['balance']['available_balance'] ?? 0, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100">
                                <i class="fas fa-money-bill-wave text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Withdrawn Card -->
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Withdrawn</p>
                                <p class="text-xl md:text-2xl font-semibold text-gray-900">
                                    Rp {{ number_format($balanceData['balance']['withdrawn'] ?? 0, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full bg-gray-100">
                                <i class="fas fa-arrow-down text-gray-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Withdrawal Card -->
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Pending</p>
                                <p class="text-xl md:text-2xl font-semibold text-yellow-600">
                                    Rp {{ number_format($balanceData['balance']['pending_withdrawal'] ?? 0, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full bg-yellow-100">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Platform Fee Info -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-2">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        Platform Fee Deducted: Rp {{ number_format($balanceData['balance']['platform_fee_total'] ?? 0, 0, ',', '.') }} (5%)
                    </p>
                </div>
            </div>
            @endif
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6 mt-4">
        <nav class="-mb-px flex space-x-8">
            <button class="tab-btn border-b-2 border-[#B22234] text-[#B22234] pb-4 px-1 text-sm font-medium" data-tab="revenue">
                <i class="fas fa-chart-line mr-2"></i>Revenue Overview
            </button>
            <button class="tab-btn border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 pb-4 px-1 text-sm font-medium" data-tab="withdrawals">
                <i class="fas fa-money-check-alt mr-2"></i>Withdrawal History
            </button>
        </nav>
    </div>

    <!-- Tab Content: Revenue Overview -->
    <div id="revenue-tab" class="tab-content">
        <!-- Revenue Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-gray-500 text-sm">Total Events</div>
                <div class="text-2xl font-bold">{{ number_format($summary['total_events']) }}</div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-gray-500 text-sm">Paid Registrations</div>
                <div class="text-2xl font-bold">{{ number_format($summary['total_paid_registrations']) }}</div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-gray-500 text-sm">Total Revenue</div>
                <div class="text-2xl font-bold text-green-600">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white rounded-lg p-4 shadow">
                <div class="text-gray-500 text-sm">Pending Payments</div>
                <div class="text-2xl font-bold text-yellow-600">{{ number_format($summary['pending_payments']) }}</div>
            </div>
        </div>

        <!-- Events Revenue Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Registrants</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($events as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $event['title'] ?? 'Untitled Event' }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ $event['id'] ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ isset($event['start_date']) ? \Carbon\Carbon::parse($event['start_date'])->format('M d, Y H:i') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($event['price'] ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $event['registered_count'] ?? 0 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                    <!-- Details link disabled for MVP - Revenue tracking needs to be fixed -->
                                    <!-- Will be re-enabled after fixing payment status tracking logic -->
                                    <!-- <a href="{{ route('admin.events.finance', $event['id']) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-chart-line mr-1"></i>Details
                                    </a> -->
                                    {{-- <span class="text-gray-400 text-xs italic">Coming Soon</span> --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Content: Withdrawal History -->
    <div id="withdrawals-tab" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if(count($withdrawals) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Requested</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admin Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($withdrawals as $wd)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ \Carbon\Carbon::parse($wd['requested_at'])->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-green-600">
                                Rp {{ number_format($wd['amount'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $wd['bank_name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $wd['bank_account_number'] }}<br>
                                <span class="text-xs">{{ $wd['bank_account_holder'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($wd['status'] === 'pending')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </span>
                                @elseif($wd['status'] === 'approved')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Approved
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i> Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $wd['admin_notes'] ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-8 text-center">
                <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-600">No withdrawal requests yet</p>
                <p class="text-sm text-gray-500 mt-2">Click "Request Withdrawal" to create your first withdrawal request</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Withdrawal Request Modal -->
<div id="withdrawalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Request Withdrawal</h3>
                <button onclick="closeWithdrawalModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('admin.finance.withdrawals.request') }}" method="POST">
                @csrf

                <!-- Available Balance Display -->
                @if($balanceData)
                <div class="bg-blue-50 p-4 rounded-md mb-4">
                    <p class="text-sm text-blue-700">Available Balance:
                        <span class="font-semibold">Rp {{ number_format($balanceData['balance']['available_balance'] ?? 0, 0, ',', '.') }}</span>
                    </p>
                </div>
                @endif

                <!-- Amount -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount to Withdraw *</label>
                    <input type="number" name="amount"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#B22234] @error('amount') border-red-500 @enderror"
                           min="50000" step="1000" required>
                    <p class="text-xs text-gray-500 mt-1">Minimum: Rp 50,000</p>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bank Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bank Name *</label>
                    <input type="text" name="bank_name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#B22234] @error('bank_name') border-red-500 @enderror"
                           placeholder="e.g., BCA, Mandiri, BNI" required>
                    @error('bank_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Account Number -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bank Account Number *</label>
                    <input type="text" name="bank_account_number"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#B22234] @error('bank_account_number') border-red-500 @enderror"
                           placeholder="e.g., 1234567890" required>
                    @error('bank_account_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Account Holder -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Holder Name *</label>
                    <input type="text" name="bank_account_holder"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#B22234] @error('bank_account_holder') border-red-500 @enderror"
                           placeholder="Full name as registered" required>
                    @error('bank_account_holder')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" onclick="closeWithdrawalModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-[#B22234] text-white px-6 py-2 rounded-md hover:opacity-90">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal functions
function openWithdrawalModal() {
    document.getElementById('withdrawalModal').classList.remove('hidden');
}

function closeWithdrawalModal() {
    document.getElementById('withdrawalModal').classList.add('hidden');
}

// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tabName = this.dataset.tab;

        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-[#B22234]', 'text-[#B22234]');
            b.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab
        document.getElementById(tabName + '-tab').classList.remove('hidden');
        this.classList.remove('border-transparent', 'text-gray-500');
        this.classList.add('border-[#B22234]', 'text-[#B22234]');
    });
});
</script>
@endsection
