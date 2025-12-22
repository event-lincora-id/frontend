<!-- Withdrawal Detail Content (loaded in modal) -->
<div class="space-y-6">
    <!-- Request Information -->
    <div>
        <h4 class="text-sm font-bold text-gray-500 uppercase mb-3">Request Information</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Request ID</p>
                <p class="font-medium">WD-{{ $withdrawal['id'] ?? '0' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Date Requested</p>
                <p class="font-medium">{{ isset($withdrawal['requested_at']) ? \Carbon\Carbon::parse($withdrawal['requested_at'])->format('M d, Y H:i') : (isset($withdrawal['created_at']) ? \Carbon\Carbon::parse($withdrawal['created_at'])->format('M d, Y H:i') : '-') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
                <p class="font-medium">
                    @if(($withdrawal['status'] ?? '') === 'pending')
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i> Pending
                        </span>
                    @elseif(($withdrawal['status'] ?? '') === 'approved')
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i> Approved
                        </span>
                    @else
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i> Rejected
                        </span>
                    @endif
                </p>
            </div>
            @if(isset($withdrawal['approved_at']) && $withdrawal['approved_at'])
            <div>
                <p class="text-xs text-gray-500">Processed At</p>
                <p class="font-medium">{{ \Carbon\Carbon::parse($withdrawal['approved_at'])->format('M d, Y H:i') }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Organizer Information -->
    <div class="border-t pt-4">
        <h4 class="text-sm font-bold text-gray-500 uppercase mb-3">Organizer Information</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Name</p>
                <p class="font-medium">{{ $withdrawal['organizer']['name'] ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Email</p>
                <p class="font-medium">{{ $withdrawal['organizer']['email'] ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Withdrawal Details -->
    <div class="border-t pt-4">
        <h4 class="text-sm font-bold text-gray-500 uppercase mb-3">Withdrawal Details</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Amount</p>
                <p class="text-lg font-semibold text-green-600">Rp {{ number_format($withdrawal['amount'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Bank</p>
                <p class="font-medium">{{ $withdrawal['bank_name'] ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Account Number</p>
                <p class="font-medium">{{ $withdrawal['bank_account_number'] ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Account Holder</p>
                <p class="font-medium">{{ $withdrawal['bank_account_holder'] ?? '-' }}</p>
            </div>
        </div>
    </div>

    @if(isset($withdrawal['admin_notes']) && $withdrawal['admin_notes'])
    <!-- Admin Notes -->
    <div class="border-t pt-4">
        <h4 class="text-sm font-bold text-gray-500 uppercase mb-3">Admin Notes</h4>
        <div class="bg-gray-50 rounded-md p-4">
            <p class="text-sm text-gray-700">{{ $withdrawal['admin_notes'] }}</p>
        </div>
        @if(isset($withdrawal['approved_by_admin']) && $withdrawal['approved_by_admin'])
        <p class="text-xs text-gray-500 mt-2">
            By: {{ $withdrawal['approved_by_admin']['name'] ?? '-' }}
        </p>
        @endif
    </div>
    @endif

    @if(($withdrawal['status'] ?? '') === 'pending')
    <!-- Admin Action Form -->
    <div class="border-t pt-4">
        <h4 class="text-sm font-bold text-gray-500 uppercase mb-3">Admin Action</h4>

        <!-- Approve Form -->
        <form id="approveForm" action="{{ route('super.admin.withdrawals.approve', $withdrawal['id']) }}" method="POST" class="mb-4" onsubmit="return handleApprovalSubmit(event)">
            @csrf
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
            <textarea name="admin_notes" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#B22234]"
                      placeholder="Add any notes about this approval..."></textarea>

            <div class="flex gap-3 mt-4">
                <button type="button" onclick="closeReviewModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" onclick="confirmReject({{ $withdrawal['id'] }})" id="rejectBtn"
                        class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    <i class="fas fa-times-circle mr-2"></i>Reject
                </button>
                <button type="submit" id="approveBtn"
                        class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                    <i class="fas fa-check-circle mr-2"></i>Approve
                </button>
            </div>
        </form>
    </div>

    <script>
    let isSubmitting = false;

    function handleApprovalSubmit(event) {
        if (isSubmitting) {
            event.preventDefault();
            return false;
        }

        isSubmitting = true;
        const approveBtn = document.getElementById('approveBtn');
        const rejectBtn = document.getElementById('rejectBtn');

        // Disable buttons and show loading state
        approveBtn.disabled = true;
        rejectBtn.disabled = true;
        approveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        approveBtn.classList.add('opacity-50', 'cursor-not-allowed');

        return true;
    }

    function confirmReject(id) {
        if (isSubmitting) {
            return;
        }

        const notes = prompt('Please enter rejection reason (required):');
        if (notes && notes.trim()) {
            isSubmitting = true;
            const rejectBtn = document.getElementById('rejectBtn');
            const approveBtn = document.getElementById('approveBtn');

            // Disable buttons
            rejectBtn.disabled = true;
            approveBtn.disabled = true;
            rejectBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Rejecting...';
            rejectBtn.classList.add('opacity-50', 'cursor-not-allowed');

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/super-admin/withdrawals/${id}/reject`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            const notesInput = document.createElement('input');
            notesInput.type = 'hidden';
            notesInput.name = 'admin_notes';
            notesInput.value = notes;

            form.appendChild(csrf);
            form.appendChild(notesInput);
            document.body.appendChild(form);
            form.submit();
        } else if (notes !== null) {
            alert('Rejection reason is required');
        }
    }
    </script>
    @else
    <!-- View Only Mode for Approved/Rejected -->
    <div class="border-t pt-4">
        <div class="flex gap-3 justify-end">
            <button type="button" onclick="closeReviewModal()"
                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>
    @endif
</div>
