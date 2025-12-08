<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display notifications page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Notification::with(['event'])
            ->where('user_id', $user->id);

        // Filter by type if specified
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->unread();
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        // Return JSON for testing purposes or if requested via AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $notifications
            ]);
        }

        // Determine which view to return based on user role
        if ($user->role === 'admin') {
            return view('admin.notifications.index', compact('notifications'));
        }

        return view('participant.notifications.index', compact('notifications'));
    }

    /**
     * Get unread notification count (AJAX)
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Mark notification as read (AJAX)
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $user = $request->user();

        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi telah dibaca'
        ]);
    }

    /**
     * Mark all notifications as read (AJAX)
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $updated = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah dibaca',
            'updated_count' => $updated
        ]);
    }

    /**
     * Delete notification (AJAX)
     */
    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $user = $request->user();

        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dihapus'
        ]);
    }
}