<?php

namespace App\Http\Controllers;

use App\Services\BackendApiService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ParticipantEventController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Transform event data from API to object with all needed properties
     */
    private function transformEvent($event): object
{
    // Handle if event is just an ID
    if (is_int($event) || is_string($event)) {
        return (object) [
            'id' => $event,
            'title' => 'Event #' . $event,
            'description' => '',
            'image' => null,
            'location' => 'No Location',
            'price' => 0,
            'status' => 'draft',
            'start_date' => \Carbon\Carbon::now(),
            'end_date' => \Carbon\Carbon::now(),
            'category' => null,
            'organizer' => null,
            'quota' => 0,
            'registered_count' => 0,
        ];
    }
    
    // Convert array to object
    if (is_array($event)) {
        $event = (object) $event;
    }
    
    // Convert nested arrays to objects
    $category = null;
    if (isset($event->category)) {
        $category = is_array($event->category) ? (object) $event->category : $event->category;
    }
    
    $organizer = null;
    if (isset($event->organizer)) {
        $organizer = is_array($event->organizer) ? (object) $event->organizer : $event->organizer;
    }
    
    // Create new object with guaranteed properties
    return (object) [
        'id' => $event->id ?? null,
        'title' => $event->title ?? 'No Title',
        'description' => $event->description ?? '',
        'image' => $event->image ?? null,
        'image_url' => $event->image_url ?? $this->api->assetUrl($event->image ?? null),
        'location' => $event->location ?? 'No Location',
        'price' => $event->price ?? 0,
        'status' => $event->status ?? 'draft',
        'category' => $category,
        'category_name' => $category->name ?? null,
        'organizer' => $organizer,
        'organizer_name' => $organizer->name ?? null,
        'max_participants' => $event->max_participants ?? 0,
        'current_participants' => $event->current_participants ?? 0,
        'participants_count' => $event->registered_count ?? $event->current_participants ?? 0,
        'quota' => $event->quota ?? $event->max_participants ?? 0,
        'registered_count' => $event->registered_count ?? $event->current_participants ?? 0,
        'start_date' => isset($event->start_date)
            ? \Carbon\Carbon::parse($event->start_date)
            : \Carbon\Carbon::now(),
        'end_date' => isset($event->end_date)
            ? \Carbon\Carbon::parse($event->end_date)
            : (isset($event->start_date) ? \Carbon\Carbon::parse($event->start_date) : \Carbon\Carbon::now()),
    ];
}

    /**
     * Display home page with event recommendations
     */
    public function home(): View
{
    try {
        Log::info('Fetching events from API for home page...');

        // Get events from API with caching (5 minutes) and reduced count for faster load
        $eventsResponse = Cache::remember('home_events', 300, function() {
            return $this->api->get('events', ['per_page' => 12]);
        });
        Log::info('API events response:', ['has_data' => isset($eventsResponse['data'])]);
        Log::info('API raw response structure:', [
            'keys' => array_keys($eventsResponse),
            'has_success' => isset($eventsResponse['success']),
            'success_value' => $eventsResponse['success'] ?? null,
            'data_type' => isset($eventsResponse['data']) ? gettype($eventsResponse['data']) : 'none',
            'data_keys' => isset($eventsResponse['data']) && is_array($eventsResponse['data']) ? array_keys($eventsResponse['data']) : [],
            'has_data_data' => isset($eventsResponse['data']['data']),
            'data_data_count' => isset($eventsResponse['data']['data']) ? count($eventsResponse['data']['data']) : 0
        ]);
        
        // Parse events data - API returns {success: true, data: {data: [...]}}
        $eventsData = [];
        
        // Handle success wrapper from API
        if (isset($eventsResponse['success']) && $eventsResponse['success']) {
            if (isset($eventsResponse['data']['data'])) {
                $eventsData = $eventsResponse['data']['data'];
            } elseif (isset($eventsResponse['data']) && is_array($eventsResponse['data'])) {
                $eventsData = $eventsResponse['data'];
            }
        } elseif (isset($eventsResponse['data']['data'])) {
            $eventsData = $eventsResponse['data']['data'];
        } elseif (isset($eventsResponse['data']) && is_array($eventsResponse['data'])) {
            $eventsData = $eventsResponse['data'];
        }
        
        Log::info('Events found:', ['count' => count($eventsData)]);
        
        // Transform events to objects
        $events = collect($eventsData)
            ->map(fn($event) => $this->transformEvent($event));

        // Get categories from API
        $categoriesResponse = $this->api->get('categories');
        $categoriesData = $categoriesResponse['data']['data'] ?? $categoriesResponse['data'] ?? [];
        
        $categories = collect($categoriesData)
            ->map(fn($cat) => is_array($cat) ? (object) $cat : $cat);

        Log::info('Home page data loaded:', [
            'events_count' => $events->count(),
            'categories_count' => $categories->count()
        ]);

        return view('participant.events.home', compact('events', 'categories'));
        
    } catch (\Exception $e) {
        Log::error('API Error in home: ' . $e->getMessage());
        
        return view('participant.events.home', [
            'events' => collect([]),
            'categories' => collect([]),
            'error' => 'Tidak dapat memuat data dari server'
        ]);
    }
}

    /**
 * Display events with search and filter (Explore page)
 */
public function index(Request $request): View
{
    try {
        Log::info('Explore page request:', [
            'search' => $request->search,
            'category' => $request->category_id,
            'page' => $request->page
        ]);
        
        // Build API query parameters
        // Include past events so users can browse and view event history
        $params = [
            'per_page' => 20,
            'include_past' => true
        ];

        // Add search parameter
        if ($request->filled('search')) {
            $params['search'] = $request->search;
            Log::info('Search applied:', ['term' => $request->search]);
        }

        // Add category filter
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $params['category_id'] = $request->category_id;
            Log::info('Category filter applied:', ['category_id' => $request->category_id]);
        }
        
        // Add pagination
        if ($request->filled('page')) {
            $params['page'] = $request->page;
        }

        // Get events from API with filters
        $eventsResponse = $this->api->get('events', $params);
        Log::info('API response received:', [
            'has_data' => isset($eventsResponse['data']),
            'params_sent' => $params
        ]);
        
        // Parse events data - API returns {success: true, data: {data: [...]}}
        $eventsData = [];
        
        // Handle success wrapper from API
        if (isset($eventsResponse['success']) && $eventsResponse['success']) {
            if (isset($eventsResponse['data']['data'])) {
                $eventsData = $eventsResponse['data']['data'];
            } elseif (isset($eventsResponse['data']) && is_array($eventsResponse['data'])) {
                $eventsData = $eventsResponse['data'];
            }
        } elseif (isset($eventsResponse['data']['data'])) {
            $eventsData = $eventsResponse['data']['data'];
        } elseif (isset($eventsResponse['data']) && is_array($eventsResponse['data'])) {
            $eventsData = $eventsResponse['data'];
        }
        
        Log::info('Events parsed:', ['count' => count($eventsData)]);
        
        // Transform events to objects
        $events = collect($eventsData)
            ->map(fn($event) => $this->transformEvent($event));

        // Get categories from API for filter dropdown
        $categoriesResponse = $this->api->get('categories');
        $categoriesData = $categoriesResponse['data']['data'] ?? $categoriesResponse['data'] ?? [];
        
        $categories = collect($categoriesData)
            ->map(fn($cat) => is_array($cat) ? (object) $cat : $cat);

        Log::info('Explore page loaded:', [
            'events_count' => $events->count(),
            'categories_count' => $categories->count(),
            'active_filters' => [
                'search' => $request->search ?? 'none',
                'category' => $request->category_id ?? 'none'
            ]
        ]);

        return view('participant.events.index', [
            'events' => $events,
            'categories' => $categories,
            'currentSearch' => $request->search,
            'currentCategory' => $request->category_id
        ]);
    } catch (\Exception $e) {
        Log::error('API Error in explore: ' . $e->getMessage());
        
        return view('participant.events.index', [
            'events' => collect([]),
            'categories' => collect([]),
            'currentSearch' => $request->search,
            'currentCategory' => $request->category_id,
            'error' => 'Tidak dapat memuat data dari server'
        ]);
    }
}

/**
 * Display event details
 */
public function show($id): View
{
    try {
        Log::info("Fetching event details for ID: {$id}");
        
        $eventResponse = $this->api->get("events/{$id}");
        Log::info("Event detail response:", ['response' => $eventResponse]);
        
        $eventData = $eventResponse['data'] ?? null;

        if (!$eventData) {
            Log::error("No event data found for ID: {$id}");
            abort(404, 'Event not found');
        }

        $event = $this->transformEvent($eventData);
        
        // Check if user is already participating
        $isParticipating = false;
        $userParticipation = null;
        
        $token = session('api_token');
        $user = session('user');
        
        if ($token && $user) {
            try {
                // Check user's participations
                $participationsResponse = $this->api->withToken($token)->get('participants/my-participations');
                $participations = $participationsResponse['data']['data'] ?? $participationsResponse['data'] ?? [];
                
                foreach ($participations as $participation) {
                    $eventId = is_array($participation) 
                        ? ($participation['event_id'] ?? ($participation['event']['id'] ?? null))
                        : ($participation->event_id ?? ($participation->event->id ?? null));
                    
                    if ($eventId == $id) {
                        $isParticipating = true;
                        $userParticipation = is_array($participation) ? (object) $participation : $participation;
                        break;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to check participation status: ' . $e->getMessage());
            }
        }
        
        Log::info("Event transformed successfully:", [
            'id' => $event->id,
            'title' => $event->title,
            'is_participating' => $isParticipating
        ]);

        return view('participant.events.show', compact('event', 'isParticipating', 'userParticipation'));
    } catch (\Exception $e) {
        Log::error('API Error in show: ' . $e->getMessage(), [
            'id' => $id,
            'exception' => get_class($e)
        ]);
        abort(404, 'Event not found');
    }
}
}