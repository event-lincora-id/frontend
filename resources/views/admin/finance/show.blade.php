<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Details - {{ $event['title'] ?? 'Event' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="flex h-screen">
    <div class="w-64 bg-white shadow-lg">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-800">Event Connect</h1>
            <p class="text-gray-600 text-sm">Admin Dashboard</p>
        </div>
        <nav class="mt-6">
            <a href="/admin/dashboard" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50"><i class="fas fa-tachometer-alt mr-3"></i>Dashboard</a>
            <a href="/admin/finance" class="flex items-center px-6 py-3 text-gray-700 bg-blue-50 border-r-4 border-blue-500"><i class="fas fa-coins mr-3"></i>Finance</a>
            <a href="/admin/analytics" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50"><i class="fas fa-chart-line mr-3"></i>Analytics</a>
            <form action="{{ route('logout') }}" method="POST" class="mt-6 px-6">
                @csrf
                <button type="submit" class="w-full flex items-center justify-start px-0 py-3 text-left text-red-600 hover:bg-red-50 rounded">
                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                </button>
            </form>
        </nav>
    </div>
    <div class="flex-1 overflow-y-auto">
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">{{ $event['title'] ?? 'Event' }}</h2>
                    <p class="text-gray-600">Finance details and transactions</p>
                </div>
                <a href="/admin/finance" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded text-gray-700"><i class="fas fa-arrow-left mr-2"></i>Back</a>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="bg-white rounded-lg p-4 shadow md:col-span-1"><div class="text-gray-500 text-sm">Price</div><div class="text-xl font-bold">Rp {{ number_format($metrics['price'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white rounded-lg p-4 shadow md:col-span-1"><div class="text-gray-500 text-sm">Registered</div><div class="text-xl font-bold">{{ number_format($metrics['total_registered']) }}</div></div>
                <div class="bg-white rounded-lg p-4 shadow md:col-span-1"><div class="text-gray-500 text-sm">Paid</div><div class="text-xl font-bold text-green-600">{{ number_format($metrics['paid']) }}</div></div>
                <div class="bg-white rounded-lg p-4 shadow md:col-span-1"><div class="text-gray-500 text-sm">Revenue</div><div class="text-xl font-bold text-green-600">Rp {{ number_format($metrics['revenue'], 0, ',', '.') }}</div></div>
                <div class="bg-white rounded-lg p-4 shadow md:col-span-1"><div class="text-gray-500 text-sm">Pending</div><div class="text-xl font-bold text-yellow-600">{{ number_format($metrics['pending']) }}</div></div>
                <div class="bg-white rounded-lg p-4 shadow md:col-span-1"><div class="text-gray-500 text-sm">Failed</div><div class="text-xl font-bold text-red-600">{{ number_format($metrics['failed']) }}</div></div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($participants as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $p['user']['full_name'] ?? $p['user']['name'] ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">{{ $p['payment_reference'] ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">{{ strtoupper($p['payment_method'] ?? '-') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        @if(($p['payment_status'] ?? '')==='paid') bg-green-100 text-green-800
                                        @elseif(($p['payment_status'] ?? '')==='pending') bg-yellow-100 text-yellow-800
                                        @elseif(($p['payment_status'] ?? '')==='failed') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">{{ strtoupper($p['payment_status'] ?? '-') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ ($p['is_paid'] ?? false) ? 'text-green-600' : 'text-gray-600' }}">Rp {{ number_format($p['amount_paid'] ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ isset($p['created_at']) ? \Carbon\Carbon::parse($p['created_at'])->format('M d, Y H:i') : '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>


