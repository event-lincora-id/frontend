<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackendApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class EventParticipationController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Join/Register for an event
     */
    public function register($eventId)
    {
        try {
            Log::info('=== EVENT REGISTRATION START ===');
            Log::info('Event ID: ' . $eventId);
            
            $token = Session::get('api_token');
            $user = Session::get('user');

            Log::info('Token: ' . ($token ? 'exists' : 'null'));
            Log::info('User: ' . json_encode($user));

            if (!$token || !$user) {
                Log::warning('No token or user found, redirecting to login');
                return redirect()->route('login')
                    ->with('error', 'Silakan login terlebih dahulu');
            }

            Log::info('Calling API to join event...');
            
            // Use correct endpoint: /participants/join/{event_id}
            // Body should be empty JSON object as per Postman collection
            try {
                $response = $this->api->withToken($token)->post("participants/join/{$eventId}", []);
                
                Log::info('API Response status: ' . ($response['success'] ?? 'no success field'));
                Log::info('API Response full: ' . json_encode($response));

                if (isset($response['success']) && $response['success']) {
                    Log::info('Registration successful!');
                    
                    return redirect()->back()
                        ->with('success', 'Successfully joined the event! Check your participations for QR code.');
                }

                Log::warning('Registration failed: ' . ($response['message'] ?? 'Unknown error'));
                
                return redirect()->back()
                    ->with('error', $response['message'] ?? 'Failed to join event');
                    
            } catch (\App\Exceptions\BackendApiException $e) {
                Log::error('Backend API Error during join: ' . $e->getMessage());
                
                // Check for specific error messages from backend
                $errorMessage = $e->getMessage();
                
                if (str_contains($errorMessage, 'already registered') || str_contains($errorMessage, 'already joined')) {
                    return redirect()->back()
                        ->with('info', 'You have already joined this event.');
                }
                
                if (str_contains($errorMessage, 'full') || str_contains($errorMessage, 'quota')) {
                    return redirect()->back()
                        ->with('error', 'Sorry, this event is already full.');
                }
                
                if (str_contains($errorMessage, '500')) {
                    return redirect()->back()
                        ->with('error', 'Backend error: The event registration is currently unavailable. Please try again later or contact the event organizer.');
                }
                
                return redirect()->back()
                    ->with('error', 'Failed to join event: ' . $errorMessage);
            }

        } catch (\Exception $e) {
            Log::error('Event registration error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'An error occurred while joining the event. Please try again later.');
        }
    }

    /**
     * Show user's participations
     */
    public function myParticipations(Request $request)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Get user's participations using correct endpoint from API docs
            $participationsData = [];
            
            try {
                $response = $this->api->withToken($token)->get('participants/my-participations');
                $participationsData = $response['data']['data'] ?? $response['data'] ?? [];
                Log::info('Got ' . count($participationsData) . ' participations');
            } catch (\Exception $e) {
                Log::error('Failed to get participations: ' . $e->getMessage());
                $participationsData = [];
            }
            
            // Convert to objects
            $participations = array_map(function($participation) {
                return $this->arrayToObject($participation);
            }, $participationsData);

            return view('participant.my-events', compact('participations'));

        } catch (\Exception $e) {
            Log::error('My participations error: ' . $e->getMessage());
            return view('participant.my-events', [
                'participations' => [],
                'error' => 'Failed to load your participations: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show QR code for a specific participation
     */
    public function showQrCode($participationId)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Get participation details
            $response = $this->api->withToken($token)->get("participations/{$participationId}");
            $participationData = $response['data'] ?? null;

            if (!$participationData) {
                return redirect()->route('my.participations')
                    ->with('error', 'Participation not found');
            }

            $participation = $this->arrayToObject($participationData);

            // Generate QR code data
            $qrData = json_encode([
                'participation_id' => $participation->id ?? $participationId,
                'event_id' => $participation->event_id ?? null,
                'user_id' => $user['id'],
                'qr_code' => $participation->qr_code ?? null,
                'timestamp' => time()
            ]);

            return view('participant.qr-code', compact('participation', 'qrData'));

        } catch (\Exception $e) {
            Log::error('QR code show error: ' . $e->getMessage());
            return redirect()->route('my.participations')
                ->with('error', 'Failed to load QR code');
        }
    }

    /**
     * Cancel participation
     */
    public function cancel($participationId)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return redirect()->route('login');
            }

            $response = $this->api->withToken($token)->delete("participations/{$participationId}");

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('my.participations')
                    ->with('success', 'Successfully cancelled your participation');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Failed to cancel participation');

        } catch (\Exception $e) {
            Log::error('Cancel participation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Check attendance (for organizer)
     */
    public function checkAttendance(Request $request, $eventId)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $qrCode = $request->input('qr_code');

            if (!$qrCode) {
                return response()->json(['success' => false, 'message' => 'QR code is required'], 400);
            }

            // Submit attendance via API
            $response = $this->api->withToken($token)->post("events/{$eventId}/attendance", [
                'qr_code' => $qrCode
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Check attendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
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
                // Convert datetime strings to Carbon objects
                if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}/', $value)) {
                    try {
                        $parsed = \Carbon\Carbon::parse($value);
                        // If it's UTC (has 'Z' or '+00:00'), convert to Asia/Jakarta
                        if (str_contains($value, 'Z') || str_contains($value, '+00:00')) {
                            $object->$key = $parsed->setTimezone('Asia/Jakarta');
                        } else {
                            // Already in correct timezone, return as Carbon object
                            $object->$key = $parsed;
                        }
                    } catch (\Exception $e) {
                        $object->$key = $value;
                    }
                } else {
                    $object->$key = $value;
                }
            }
        }
        return $object;
    }
}
