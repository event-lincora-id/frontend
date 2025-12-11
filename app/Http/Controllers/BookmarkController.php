<?php

namespace App\Http\Controllers;

use App\Services\BackendApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class BookmarkController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    public function index(Request $request)
    {
        try {
            $token = Session::get('api_token');
            
            if (!$token) {
                return redirect()->route('login');
            }
            
            // Get all published events for bookmark filtering in frontend
            $eventsResponse = $this->api->withToken($token)->get('events', [
                'status' => 'published',
                'per_page' => 100
            ]);
            
            $allEvents = collect($eventsResponse['data'] ?? []);
            
            $categories = ['saved', 'upcoming', 'past', 'interested'];
            
            // Counts will be calculated in frontend from localStorage
            $counts = [
                'all' => 0,
                'saved' => 0,
                'upcoming' => 0,
                'past' => 0,
                'interested' => 0,
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $allEvents,
                    'counts' => $counts
                ]);
            }

            return view('participant.bookmarks.index', compact('allEvents', 'categories', 'counts'));
            
        } catch (\Exception $e) {
            Log::error('Bookmark index error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return view('participant.bookmarks.index', [
                'allEvents' => collect([]),
                'categories' => ['saved', 'upcoming', 'past', 'interested'],
                'counts' => [
                    'all' => 0,
                    'saved' => 0,
                    'upcoming' => 0,
                    'past' => 0,
                    'interested' => 0,
                ],
                'error' => 'Tidak dapat memuat bookmark: ' . $e->getMessage()
            ]);
        }
    }

    public function store(Request $request, $eventId): JsonResponse
    {
        try {
            $token = Session::get('api_token');
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            $response = $this->api->withToken($token)->post("bookmarks/{$eventId}", [
                'category' => $request->category ?? 'saved',
                'notes' => $request->notes,
            ]);

            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Bookmark store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat bookmark: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $eventId): JsonResponse
    {
        try {
            $token = Session::get('api_token');
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            $response = $this->api->withToken($token)->delete("bookmarks/{$eventId}");

            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Bookmark destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus bookmark: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggle(Request $request, $eventId): JsonResponse
    {
        try {
            $token = Session::get('api_token');
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            $response = $this->api->withToken($token)->post("bookmarks/{$eventId}/toggle");

            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Bookmark toggle error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal toggle bookmark: ' . $e->getMessage()
            ], 500);
        }
    }

    public function check(Request $request, $eventId): JsonResponse
    {
        try {
            $token = Session::get('api_token');
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }
            
            $response = $this->api->withToken($token)->get("bookmarks/{$eventId}/check");

            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Bookmark check error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}