<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackendApiService;

class FinanceController extends Controller
{
    protected $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    public function index(Request $request)
    {
        $token = session('api_token');

        // Fetch events from backend API
        $events = collect();
        $summary = [
            'total_events' => 0,
            'total_paid_registrations' => 0,
            'total_revenue' => 0,
            'pending_payments' => 0,
        ];

        try {
            $params = [];
            if ($request->filled('search')) {
                $params['search'] = $request->search;
            }
            if ($request->filled('status')) {
                $params['status'] = $request->status;
            }

            $eventsResponse = $this->api->get('events/my-events', $params, $token);
            $events = collect($eventsResponse['data']['data'] ?? []);

            // Calculate summary from events
            $summary['total_events'] = $eventsResponse['data']['total'] ?? 0;
        } catch (\Exception $e) {
            session()->flash('error', 'Unable to fetch events data: ' . $e->getMessage());
        }

        // Fetch balance dashboard data
        $balanceData = null;
        try {
            $balanceResponse = $this->api->get('balance/dashboard', [], $token);
            $balanceData = $balanceResponse['data'] ?? null;

            // Use balance data for accurate summary
            if ($balanceData && isset($balanceData['balance'])) {
                // Total revenue is total_earned + platform_fee_total (gross revenue before fees)
                $summary['total_revenue'] = $balanceData['balance']['total_earned'] + $balanceData['balance']['platform_fee_total'];

                // Get paid registrations from statistics
                $summary['total_paid_registrations'] = $balanceData['statistics']['paid_registrations'] ?? 0;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Unable to fetch balance data: ' . $e->getMessage());
        }

        // Fetch withdrawal history
        $withdrawals = [];
        try {
            $withdrawalsResponse = $this->api->get('withdrawals/history', [], $token);
            $withdrawals = $withdrawalsResponse['data']['withdrawals'] ?? [];
        } catch (\Exception $e) {
            // Silently fail withdrawal history fetch
        }

        return view('admin.finance.index', compact('events', 'summary', 'balanceData', 'withdrawals'));
    }

    public function withdrawalRequest(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:50000',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_holder' => 'required|string|max:255',
        ]);

        $token = session('api_token');

        try {
            $response = $this->api->post('withdrawals/request', $validated, $token);
            $amount = number_format($validated['amount'], 0, ',', '.');
            session()->flash('success', "Withdrawal request for Rp {$amount} submitted successfully! Please wait for admin approval.");
        } catch (\App\Exceptions\BackendApiException $e) {
            // Extract clean error message from API response
            $apiMessage = $e->getResponseData()['message'] ?? $e->getMessage();
            session()->flash('error', $apiMessage);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->route('admin.finance.index');
    }

    public function show(Request $request, $eventId)
    {
        $token = session('api_token');

        // Fetch event details from backend API
        try {
            $eventResponse = $this->api->get("events/{$eventId}", [], $token);
            $event = $eventResponse['data'] ?? null;

            if (!$event) {
                abort(404, 'Event not found');
            }

            // Fetch participants for this event
            $participantsResponse = $this->api->get("events/{$eventId}/participants", [], $token);
            $participants = collect($participantsResponse['data'] ?? []);

            // Calculate metrics from participants data
            $metrics = [
                'price' => $event['price'] ?? 0,
                'total_registered' => $event['registered_count'] ?? 0,
                'paid' => $participants->where('is_paid', true)->count(),
                'revenue' => $participants->where('is_paid', true)->sum('amount_paid'),
                'pending' => $participants->where('payment_status', 'pending')->count(),
                'failed' => $participants->where('payment_status', 'failed')->count(),
                'cancelled' => $participants->where('status', 'cancelled')->count(),
            ];

            return view('admin.finance.show', compact('event', 'participants', 'metrics'));
        } catch (\Exception $e) {
            abort(500, 'Unable to fetch event data: ' . $e->getMessage());
        }
    }
}
