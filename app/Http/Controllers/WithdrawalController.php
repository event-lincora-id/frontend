<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackendApiService;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
{
    protected $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    public function index(Request $request)
    {
        $token = session('api_token');
        $filters = $request->only(['status', 'date_from', 'date_to']);

        try {
            $response = $this->api->withToken($token)->get('admin/withdrawals', $filters);
            $withdrawals = $response['data']['withdrawals'] ?? [];

            // Use summary stats from backend API if available, otherwise calculate manually
            $backendSummary = $response['data']['summary'] ?? null;

            if ($backendSummary) {
                $stats = [
                    'pending_count' => $backendSummary['pending'] ?? 0,
                    'pending_amount' => $backendSummary['total_amount_pending'] ?? 0,
                    'approved_count' => $backendSummary['approved'] ?? 0,
                    'approved_amount' => $backendSummary['total_amount_approved'] ?? 0,
                    'rejected_count' => $backendSummary['rejected'] ?? 0,
                    'rejected_amount' => 0, // Backend doesn't provide rejected amount yet
                ];
            } else {
                // Fallback: Calculate summary stats manually
                $stats = [
                    'pending_count' => collect($withdrawals)->where('status', 'pending')->count(),
                    'pending_amount' => collect($withdrawals)->where('status', 'pending')->sum('amount'),
                    'approved_count' => collect($withdrawals)->where('status', 'approved')->count(),
                    'approved_amount' => collect($withdrawals)->where('status', 'approved')->sum('amount'),
                    'rejected_count' => collect($withdrawals)->where('status', 'rejected')->count(),
                    'rejected_amount' => collect($withdrawals)->where('status', 'rejected')->sum('amount'),
                ];
            }
        } catch (\Exception $e) {
            $withdrawals = [];
            $stats = [];
            session()->flash('error', 'Unable to fetch withdrawals: ' . $e->getMessage());
        }

        return view('admin.super.withdrawals.index', compact('withdrawals', 'stats'));
    }

    public function show($id)
    {
        $token = session('api_token');

        try {
            $response = $this->api->withToken($token)->get("admin/withdrawals/{$id}");
            $withdrawal = $response['data']['withdrawal_request'] ?? null;
            $organizerBalance = $response['data']['organizer_balance'] ?? null;
        } catch (\Exception $e) {
            abort(404, 'Withdrawal not found');
        }

        return view('admin.super.withdrawals.show', compact('withdrawal', 'organizerBalance'));
    }

    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $token = session('api_token');

        try {
            $this->api->withToken($token)->post("admin/withdrawals/{$id}/approve", $validated);
            session()->flash('success', 'Withdrawal approved successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to approve withdrawal: ' . $e->getMessage());
        }

        return redirect()->route('super.admin.withdrawals.index');
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $token = session('api_token');

        try {
            $this->api->withToken($token)->post("admin/withdrawals/{$id}/reject", $validated);
            session()->flash('success', 'Withdrawal rejected successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reject withdrawal: ' . $e->getMessage());
        }

        return redirect()->route('super.admin.withdrawals.index');
    }
}
