<?php

namespace App\Http\Controllers;

use App\Services\BackendApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Create payment for event participation
     */
    public function createPayment(Request $request, $eventId): JsonResponse
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            Log::info('Creating payment for event', [
                'event_id' => $eventId,
                'payment_method' => 'invoice'
            ]);

            // Always use invoice payment method for direct Xendit redirect
            $response = $this->api->withToken($token)->post("payments/create", [
                'event_id' => $eventId,
                'payment_method' => 'invoice'
            ]);

            Log::info('Payment creation response', [
                'success' => $response['success'] ?? false,
                'has_payment_url' => isset($response['data']['payment_url'])
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Payment creation error', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show payment success page
     */
    public function success(Request $request): View
    {
        $participantId = $request->get('participant_id');
        $status = $request->get('status', 'success');

        $participant = null;
        $event = null;

        if ($participantId) {
            try {
                $token = Session::get('api_token');

                if ($token) {
                    // Fetch participant data from backend API
                    $response = $this->api->withToken($token)->get("payments/status/{$participantId}");

                    if ($response['success'] ?? false) {
                        $participantData = $response['data']['participant'] ?? null;
                        $eventData = $response['data']['event'] ?? null;

                        if ($participantData) {
                            $participant = $this->arrayToObject($participantData);
                        }

                        if ($eventData) {
                            $event = $this->arrayToObject($eventData);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch participant data for success page', [
                    'participant_id' => $participantId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('payments.success', compact('participant', 'event', 'status'));
    }

    /**
     * Show payment failure page
     */
    public function failure(Request $request): View
    {
        $participantId = $request->get('participant_id');
        $status = $request->get('status', 'failed');

        $participant = null;
        $event = null;

        if ($participantId) {
            try {
                $token = Session::get('api_token');

                if ($token) {
                    // Fetch participant data from backend API
                    $response = $this->api->withToken($token)->get("payments/status/{$participantId}");

                    if ($response['success'] ?? false) {
                        $participantData = $response['data']['participant'] ?? null;
                        $eventData = $response['data']['event'] ?? null;

                        if ($participantData) {
                            $participant = $this->arrayToObject($participantData);
                        }

                        if ($eventData) {
                            $event = $this->arrayToObject($eventData);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch participant data for failure page', [
                    'participant_id' => $participantId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('payments.failure', compact('participant', 'event', 'status'));
    }

    /**
     * Show payment status page
     */
    public function status(Request $request, $participantId): View
    {
        $participant = null;

        try {
            $token = Session::get('api_token');

            if ($token) {
                // Fetch participant data from backend API
                $response = $this->api->withToken($token)->get("payments/status/{$participantId}");

                if ($response['success'] ?? false) {
                    $participantData = $response['data']['participant'] ?? null;

                    if ($participantData) {
                        $participant = $this->arrayToObject($participantData);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch participant data for status page', [
                'participant_id' => $participantId,
                'error' => $e->getMessage()
            ]);
        }

        return view('payments.status', compact('participant'));
    }

    /**
     * Helper: Convert array to object recursively
     */
    private function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $object = new \stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $object->$key = $this->arrayToObject($value);
            } else {
                $object->$key = $value;
            }
        }
        return $object;
    }
}