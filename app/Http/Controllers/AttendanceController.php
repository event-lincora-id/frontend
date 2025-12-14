<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Services\BackendApiService;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }
    /**
     * Show QR scanner page for participant
     */
    public function scanner()
    {
        return view('participant.attendance.scanner');
    }

    /**
     * Show QR code display for Event Organizer
     */
    public function showQRCode($eventId)
    {
        try {
            $token = Session::get('api_token');

            $response = $this->api->withToken($token)->get("events/{$eventId}");
            $eventData = $response['data'] ?? null;

            if (!$eventData) {
                abort(404, 'Event not found');
            }

            $event = $this->arrayToObject($eventData);

            return view('admin.events.qr-code', compact('event'));
        } catch (\Exception $e) {
            Log::error('Show QR Code error: ' . $e->getMessage());
            abort(500, 'Unable to load QR Code page');
        }
    }

    /**
     * Mark attendance via web interface (for participant)
     */
    public function markAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $token = Session::get('api_token');
            $response = $this->api->withToken($token)->post('participants/attendance', [
                'qr_code' => $request->qr_code,
            ]);

            if (isset($response['success']) && $response['success']) {
                return redirect()->back()->with('success', $response['message'] ?? 'Attendance marked successfully!');
            }

            return redirect()->back()->with('error', $response['message'] ?? 'Failed to mark attendance');
        } catch (\Exception $e) {
            Log::error('Mark attendance error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to mark attendance: ' . $e->getMessage());
        }
    }

    /**
     * Get event participants list for organizer
     */
    public function getParticipants($eventId)
    {
        try {
            $token = Session::get('api_token');

            $eventResponse = $this->api->withToken($token)->get("events/{$eventId}");
            $eventData = $eventResponse['data'] ?? null;
            if (!$eventData) {
                abort(404, 'Event not found');
            }
            $event = $this->arrayToObject($eventData);

            $participantsResponse = $this->api->withToken($token)->get("events/{$eventId}/participants");
            $participantsData = $participantsResponse['data']['data'] ?? $participantsResponse['data'] ?? [];
            $participants = array_map(fn($p) => (object) $p, $participantsData);

            return view('admin.events.participants', compact('event', 'participants'));
        } catch (\Exception $e) {
            Log::error('Get participants error: ' . $e->getMessage());
            abort(500, 'Unable to load participants');
        }
    }

    private function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }
        $object = new \stdClass();
        foreach ($array as $key => $value) {
            $object->$key = is_array($value) ? $this->arrayToObject($value) : $value;
        }
        return $object;
    }
}

