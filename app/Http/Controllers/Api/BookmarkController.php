<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventBookmark;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookmarkController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's bookmarks
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = EventBookmark::with(['event.category', 'event.organizer'])
            ->where('user_id', $user->id);

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter upcoming/past
        if ($request->has('filter')) {
            if ($request->filter === 'upcoming') {
                $query->upcoming();
            } elseif ($request->filter === 'past') {
                $query->past();
            }
        }

        $bookmarks = $query->orderBy('created_at', 'desc')->paginate(12);

        // Count per category
        $counts = [
            'all' => EventBookmark::where('user_id', $user->id)->count(),
            'saved' => EventBookmark::where('user_id', $user->id)->where('category', 'saved')->count(),
            'upcoming' => EventBookmark::where('user_id', $user->id)->upcoming()->count(),
            'past' => EventBookmark::where('user_id', $user->id)->past()->count(),
            'interested' => EventBookmark::where('user_id', $user->id)->where('category', 'interested')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $bookmarks,
            'counts' => $counts
        ]);
    }

    /**
     * Bookmark an event
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'category' => 'nullable|in:saved,upcoming,past,interested',
            'notes' => 'nullable|string|max:500'
        ]);

        // Check if already bookmarked
        $existing = EventBookmark::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Event sudah di-bookmark sebelumnya'
            ], 400);
        }

        // Create bookmark
        $bookmark = EventBookmark::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'category' => $request->category ?? 'saved',
            'notes' => $request->notes,
        ]);

        // Send notification
        $categories = $this->getCategoryLabels([$request->category ?? 'saved']);
        $this->notificationService->sendBookmarkNotification($user, $event, $categories);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil di-bookmark',
            'data' => $bookmark->load('event')
        ]);
    }

    /**
     * Remove bookmark
     */
    public function destroy(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        $bookmark = EventBookmark::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if (!$bookmark) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmark tidak ditemukan'
            ], 404);
        }

        $bookmark->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bookmark berhasil dihapus'
        ]);
    }

    /**
     * Toggle bookmark
     */
    public function toggle(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        $bookmark = EventBookmark::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($bookmark) {
            // Remove bookmark
            $bookmark->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Bookmark dihapus',
                'bookmarked' => false
            ]);
        } else {
            // Add bookmark
            $category = $request->category ?? 'saved';
            
            $bookmark = EventBookmark::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'category' => $category,
                'notes' => $request->notes,
            ]);

            // Send notification
            $categories = $this->getCategoryLabels([$category]);
            $this->notificationService->sendBookmarkNotification($user, $event, $categories);

            return response()->json([
                'success' => true,
                'message' => 'Event di-bookmark',
                'bookmarked' => true,
                'data' => $bookmark->load('event')
            ]);
        }
    }

    /**
     * Check if event is bookmarked
     */
    public function check(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        $bookmark = EventBookmark::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        return response()->json([
            'success' => true,
            'bookmarked' => $bookmark !== null,
            'data' => $bookmark
        ]);
    }

    /**
     * Get category labels
     */
    private function getCategoryLabels($categories)
    {
        $labels = [
            'saved' => 'Disimpan',
            'upcoming' => 'Mendatang',
            'past' => 'Telah Lewat',
            'interested' => 'Tertarik'
        ];

        return array_map(fn($cat) => $labels[$cat] ?? $cat, $categories);
    }
}