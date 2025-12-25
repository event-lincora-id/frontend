<?php

namespace App\Http\Controllers;

use App\Services\BackendApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class NotificationController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Display notifications page
     */
    public function index(Request $request)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Get notifications from API
            $params = [];
            if ($request->has('type') && $request->type) {
                $params['type'] = $request->type;
            }
            if ($request->has('unread_only') && $request->boolean('unread_only')) {
                $params['unread_only'] = true;
            }
            
            // Add pagination params
            $params['page'] = $request->get('page', 1);
            $params['per_page'] = 20;

            $response = $this->api->withToken($token)->get('notifications', $params);

            // Check if API returns paginated data (Laravel paginator structure)
            if (isset($response['data']['data']) && is_array($response['data']['data'])) {
                // API returns paginated data - extract the notification items
                $notifications = collect($response['data']['data']);

                // Build pagination info from Laravel's paginator structure
                $pagination = [
                    'current_page' => $response['data']['current_page'] ?? 1,
                    'last_page' => $response['data']['last_page'] ?? 1,
                    'per_page' => $response['data']['per_page'] ?? 20,
                    'total' => $response['data']['total'] ?? 0,
                    'from' => $response['data']['from'] ?? null,
                    'to' => $response['data']['to'] ?? null,
                ];
            } else {
                // API returns simple array
                $notifications = collect($response['data'] ?? []);
                $pagination = null;
            }

            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $notifications,
                    'pagination' => $pagination
                ]);
            }

            // Determine which view to return based on user role
            if (in_array($user['role'] ?? '', ['admin', 'super_admin'])) {
                return view('admin.notifications.index', compact('notifications', 'pagination'));
            }

            return view('participant.notifications.index', compact('notifications', 'pagination'));

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return view('participant.notifications.index', [
                'notifications' => collect([]),
                'pagination' => null,
                'error' => 'Gagal memuat notifikasi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get unread notification count (AJAX)
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            $response = $this->api->withToken($token)->get('notifications/unread-count');

            return response()->json([
                'success' => true,
                'count' => $response['count'] ?? 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read (AJAX)
     */
    public function markAsRead(Request $request, $notification): JsonResponse
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            $response = $this->api->withToken($token)->post("notifications/{$notification}/read");

            return response()->json([
                'success' => true,
                'message' => $response['message'] ?? 'Notifikasi telah dibaca'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read (AJAX)
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            $response = $this->api->withToken($token)->post('notifications/mark-all-read');

            return response()->json([
                'success' => true,
                'message' => $response['message'] ?? 'Semua notifikasi telah dibaca',
                'updated_count' => $response['updated_count'] ?? 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete notification (AJAX)
     */
    public function destroy(Request $request, $notification): JsonResponse
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            $response = $this->api->withToken($token)->delete("notifications/{$notification}");

            return response()->json([
                'success' => true,
                'message' => $response['message'] ?? 'Notifikasi berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}