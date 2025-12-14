<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackendApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    public function index()
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Check if super admin - redirect to users list
            if (isset($user['role']) && $user['role'] === 'super_admin') {
                return redirect()->route('admin.users.index');
            }

            // Get dashboard stats from API
            $stats = $this->getDashboardStats($token);
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($token);
            
            // Get monthly event data
            $monthlyEvents = $this->getMonthlyEventData($token);
            
            // Get category stats
            $categoryStats = $this->getCategoryStats($token);
            
            // Get user trends (simplified)
            $userTrends = [];
            
            // Get top events
            $topEvents = $this->getTopEvents($token);
            
            return view('admin.dashboard', compact(
                'stats', 
                'recentActivities', 
                'monthlyEvents', 
                'categoryStats', 
                'userTrends', 
                'topEvents'
            ));
        } catch (\Exception $e) {
            Log::error('Admin Dashboard error: ' . $e->getMessage());
            
            // Return view with empty data
            return view('admin.dashboard', [
                'stats' => [
                    'total_users' => 0,
                    'total_events' => 0,
                    'total_categories' => 0,
                    'total_participants' => 0,
                    'total_feedbacks' => 0,
                    'active_events' => 0,
                    'completed_events' => 0,
                    'organizers' => 0,
                    'this_month_events' => 0,
                    'this_month_participants' => 0,
                ],
                'recentActivities' => [],
                'monthlyEvents' => [
                    'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    'events' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                ],
                'categoryStats' => collect([]),
                'userTrends' => [],
                'topEvents' => [],
                'error' => 'Tidak dapat memuat data dashboard'
            ]);
        }
    }

    public function users()
    {
        return redirect()->route('admin.users.index');
    }

    public function events()
    {
        return redirect()->route('admin.events.index');
    }

    public function categories()
    {
        return redirect()->route('admin.categories.index');
    }

    public function analytics()
    {
        return redirect()->route('admin.analytics');
    }
    
    private function getDashboardStats($token)
    {
        try {
            $user = Session::get('user');
            
            // Try to get organizer's events from API
            $events = collect([]);
            
            try {
                $eventsResponse = $this->api->withToken($token)->get('events/my-events', ['per_page' => 100]);
                $events = collect($eventsResponse['data']['data'] ?? $eventsResponse['data'] ?? []);
            } catch (\Exception $e) {
                Log::warning('events/my-events failed, trying /events: ' . $e->getMessage());

                // Fallback: get all events and filter by user
                try {
                    $eventsResponse = $this->api->withToken($token)->get('events', ['per_page' => 100]);
                    $allEvents = collect($eventsResponse['data']['data'] ?? $eventsResponse['data'] ?? []);
                    
                    Log::info('Fetched from /events: ' . $allEvents->count() . ' total events');
                    
                    // Filter by current user (organizer)
                    $userId = $user['id'] ?? null;
                    if ($userId) {
                        $events = $allEvents->filter(function($event) use ($userId) {
                            $eventUserId = $event['user_id'] ?? $event['organizer_id'] ?? null;
                            return $eventUserId == $userId;
                        })->values();
                        
                        Log::info('Filtered events for user ' . $userId . ': ' . $events->count() . ' events');
                    }
                } catch (\Exception $e2) {
                    Log::error('Both endpoints failed: ' . $e2->getMessage());
                }
            }
            
            // Get categories
            $categoriesResponse = $this->api->get('categories');
            $categories = collect($categoriesResponse['data']['data'] ?? []);
            
            // Calculate stats
            $now = Carbon::now();
            
            // Calculate total participants from all events
            $totalParticipants = $events->sum(function($event) {
                return $event['registered_count'] ?? 0;
            });

            return [
                'total_users' => $totalParticipants, // Total participants across all events
                'total_events' => $events->count(),
                'total_categories' => $categories->count(),
                'total_participants' => $totalParticipants,
                'total_feedbacks' => 0, // API endpoint belum ada
                'active_events' => $events->filter(function($event) use ($now) {
                    return isset($event['start_date']) && Carbon::parse($event['start_date'])->isAfter($now);
                })->count(),
                'completed_events' => $events->filter(function($event) use ($now) {
                    return isset($event['end_date']) && Carbon::parse($event['end_date'])->isBefore($now);
                })->count(),
                'organizers' => 1,
                'this_month_events' => $events->filter(function($event) use ($now) {
                    return isset($event['created_at']) && Carbon::parse($event['created_at'])->month === $now->month;
                })->count(),
                'this_month_participants' => $events->filter(function($event) use ($now) {
                    return isset($event['created_at']) && Carbon::parse($event['created_at'])->month === $now->month;
                })->sum(function($event) {
                    return $event['registered_count'] ?? 0;
                }),
            ];
        } catch (\Exception $e) {
            Log::error('Get dashboard stats error: ' . $e->getMessage());
            return [
                'total_users' => 0,
                'total_events' => 0,
                'total_categories' => 0,
                'total_participants' => 0,
                'total_feedbacks' => 0,
                'active_events' => 0,
                'completed_events' => 0,
                'organizers' => 0,
                'this_month_events' => 0,
                'this_month_participants' => 0,
            ];
        }
    }

    private function getRecentActivities($token)
    {
        try {
            // Simplified - return dummy data karena API endpoint belum ada
            return [];
        } catch (\Exception $e) {
            Log::error('Get recent activities error: ' . $e->getMessage());
            return [];
        }
    }

    private function getMonthlyEventData($token)
    {
        try {
            $user = Session::get('user');
            
            // Get organizer's events
            $events = collect([]);
            
            try {
                $eventsResponse = $this->api->withToken($token)->get('events/my-events', ['per_page' => 100]);
                $events = collect($eventsResponse['data']['data'] ?? $eventsResponse['data'] ?? []);
            } catch (\Exception $e) {
                // Fallback to /events with filter
                try {
                    $eventsResponse = $this->api->withToken($token)->get('events', ['per_page' => 100]);
                    $allEvents = collect($eventsResponse['data']['data'] ?? $eventsResponse['data'] ?? []);
                    $userId = $user['id'] ?? null;
                    if ($userId) {
                        $events = $allEvents->filter(fn($e) => isset($e['user_id']) && $e['user_id'] == $userId)->values();
                    }
                } catch (\Exception $e2) {
                    Log::error('Monthly event data fetch failed: ' . $e2->getMessage());
                }
            }
            
            // Initialize months array
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $eventCounts = array_fill(0, 12, 0);
            
            // Count events per month
            foreach ($events as $event) {
                if (isset($event['created_at'])) {
                    $month = Carbon::parse($event['created_at'])->month - 1; // 0-indexed
                    if ($month >= 0 && $month < 12) {
                        $eventCounts[$month]++;
                    }
                }
            }
            
            return [
                'months' => $months,
                'events' => $eventCounts,
            ];
        } catch (\Exception $e) {
            Log::error('Get monthly event data error: ' . $e->getMessage());
            return [
                'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'events' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            ];
        }
    }

    private function getCategoryStats($token)
    {
        try {
            $categoriesResponse = $this->api->get('categories');
            $categories = collect($categoriesResponse['data']['data'] ?? []);
            
            return $categories->map(function($category) {
                return (object)[
                    'name' => $category['name'] ?? 'Unknown',
                    'count' => $category['events_count'] ?? 0,
                    'color' => $category['color'] ?? '#3B82F6',
                ];
            });
        } catch (\Exception $e) {
            Log::error('Get category stats error: ' . $e->getMessage());
            return collect([]);
        }
    }

    private function getTopEvents($token)
    {
        try {
            $user = Session::get('user');
            
            $events = collect([]);
            
            try {
                $eventsResponse = $this->api->withToken($token)->get('events/my-events', ['per_page' => 100]);
                $events = collect($eventsResponse['data']['data'] ?? $eventsResponse['data'] ?? []);
            } catch (\Exception $e) {
                // Fallback to /events with filter
                try {
                    $eventsResponse = $this->api->withToken($token)->get('events', ['per_page' => 100]);
                    $allEvents = collect($eventsResponse['data']['data'] ?? $eventsResponse['data'] ?? []);
                    $userId = $user['id'] ?? null;
                    if ($userId) {
                        $events = $allEvents->filter(fn($e) => isset($e['user_id']) && $e['user_id'] == $userId)->values();
                    }
                } catch (\Exception $e2) {
                    Log::error('Top events fetch failed: ' . $e2->getMessage());
                }
            }
            
            return $events
                ->sortByDesc('registered_count')
                ->take(5)
                ->map(function($event) {
                    return (object)[
                        'id' => $event['id'] ?? null,
                        'title' => $event['title'] ?? 'Untitled',
                        'participants_count' => $event['registered_count'] ?? 0,
                        'start_date' => isset($event['start_date']) ? Carbon::parse($event['start_date']) : null,
                        'organizer' => (object)[
                            'full_name' => $event['organizer']['full_name'] ?? $event['organizer']['name'] ?? 'Unknown',
                        ],
                        'category' => (object)[
                            'color' => $event['category']['color'] ?? '#3B82F6',
                        ],
                    ];
                })
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Get top events error: ' . $e->getMessage());
            return [];
        }
    }
}