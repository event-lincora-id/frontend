<?php

namespace App\Http\Controllers;

use App\Services\BackendApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Show feedback form for an event
     */
    public function create($eventId)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Convert user to object if it's an array
            if (is_array($user)) {
                $user = (object) $user;
            }

            // Get event details from API
            $eventResponse = $this->api->withToken($token)->get("events/{$eventId}");

            if (!isset($eventResponse['success']) || !$eventResponse['success']) {
                return redirect()->route('participant.dashboard')
                    ->with('error', 'Event not found');
            }

            $event = (object) $eventResponse['data'];

            // Get user's participation for this event
            $participationResponse = $this->api->withToken($token)->get('participants/my-participations', [
                'per_page' => 100
            ]);

            Log::info('Participation API response for feedback:', [
                'success' => $participationResponse['success'] ?? false,
                'has_data' => isset($participationResponse['data']),
                'event_id' => $eventId
            ]);

            if (!isset($participationResponse['success']) || !$participationResponse['success']) {
                Log::error('Failed to get participations for feedback');
                return redirect()->route('participant.dashboard')
                    ->with('error', 'Unable to verify participation');
            }

            // Find participation for this specific event
            $participationsData = $participationResponse['data']['data'] ?? $participationResponse['data'];

            // Convert to array if it's an object
            if (is_object($participationsData)) {
                $participationsData = json_decode(json_encode($participationsData), true);
            }

            Log::info('Searching for participation:', [
                'total_participations' => is_array($participationsData) ? count($participationsData) : 'not array',
                'looking_for_event_id' => $eventId,
                'first_participation' => isset($participationsData[0]) ? json_encode($participationsData[0]) : 'no data'
            ]);

            $participation = collect($participationsData)->first(function($p) use ($eventId) {
                $p = (object) $p;
                $eventObj = is_object($p->event ?? null) ? $p->event : (object)($p->event ?? []);
                $matches = isset($eventObj->id) && $eventObj->id == $eventId;

                if ($matches) {
                    Log::info('Found matching participation', [
                        'event_id' => $eventObj->id,
                        'status' => $p->status ?? 'no status'
                    ]);
                }

                return $matches;
            });

            if (!$participation) {
                Log::error('Participation not found for event', ['event_id' => $eventId]);
                return redirect()->route('participant.dashboard')
                    ->with('error', 'You are not registered for this event');
            }

            // Convert to object if needed
            $participation = is_object($participation) ? $participation : (object) $participation;

            Log::info('Checking attendance status:', [
                'status' => $participation->status ?? 'no status',
                'is_attended' => ($participation->status ?? '') === 'attended'
            ]);

            // Check if user has attended the event
            if (($participation->status ?? '') !== 'attended') {
                return redirect()->route('participant.dashboard')
                    ->with('error', 'You must attend the event before giving feedback. Current status: ' . ($participation->status ?? 'unknown'));
            }

            // Check if feedback already submitted
            $feedbacksResponse = $this->api->withToken($token)->get('feedbacks/my-feedbacks');
            if (isset($feedbacksResponse['success']) && $feedbacksResponse['success']) {
                $existingFeedback = collect($feedbacksResponse['data'])->first(function($f) use ($eventId) {
                    return isset($f->event_id) && $f->event_id == $eventId;
                });

                if ($existingFeedback) {
                    return redirect()->route('participant.dashboard')
                        ->with('info', 'You have already submitted feedback for this event');
                }
            }

            return view('participant.feedback.create', compact('event', 'user'));

        } catch (\Exception $e) {
            Log::error('Error loading feedback form:', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('participant.dashboard')
                ->with('error', 'Unable to load feedback form');
        }
    }

    /**
     * Store feedback
     */
    public function store(Request $request, $eventId)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return redirect()->route('login');
            }

            // Validate form data
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|min:10|max:1000',
            ]);

            // Submit feedback to API
            try {
                $response = $this->api->withToken($token)->post("feedbacks/{$eventId}", [
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                ]);

                if (!isset($response['success']) || !$response['success']) {
                    Log::error('Backend rejected feedback:', [
                        'event_id' => $eventId,
                        'response' => $response
                    ]);

                    return back()
                        ->withInput()
                        ->with('error', $response['message'] ?? 'Failed to submit feedback');
                }

                Log::info('Feedback submitted successfully:', [
                    'event_id' => $eventId,
                    'rating' => $request->rating,
                    'feedback_id' => $response['data']['id'] ?? null
                ]);

                return redirect()->route('participant.dashboard')
                    ->with('success', 'Thank you for your feedback! You can now download your certificate.');

            } catch (\App\Exceptions\BackendApiException $e) {
                Log::error('API exception while submitting feedback:', [
                    'event_id' => $eventId,
                    'error' => $e->getMessage()
                ]);

                return back()
                    ->withInput()
                    ->with('error', 'Failed to submit feedback: ' . $e->getMessage());
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error submitting feedback:', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to submit feedback');
        }
    }

    /**
     * Download certificate
     */
    public function downloadCertificate($eventId)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return redirect()->route('login');
            }

            // Get certificate from API
            $response = $this->api->withToken($token)->getRaw("feedbacks/certificate/{$eventId}/download");

            // Return the PDF response
            return response($response['body'], $response['status'])
                ->withHeaders($response['headers']);

        } catch (\Exception $e) {
            Log::error('Error downloading certificate:', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('participant.dashboard')
                ->with('error', 'Unable to download certificate');
        }
    }
}
