<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackendApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class EventController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Display a listing of events
     */
    public function index(Request $request)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Build query parameters
            $params = ['per_page' => 100]; // Get more events

            if ($request->has('search') && $request->search) {
                $params['search'] = $request->search;
            }

            if ($request->has('category_id') && $request->category_id) {
                $params['category_id'] = $request->category_id;
            }

            if ($request->has('status') && $request->status) {
                $params['status'] = $request->status;
            }

            // Try different endpoints
            $eventsData = [];

            try {
                // Try /events/my-events first
                $response = $this->api->withToken($token)->get('events/my-events', $params);
                Log::info('API Response from events/my-events:', $response);
                
                // Handle different response structures
                if (isset($response['data'])) {
                    if (isset($response['data']['data'])) {
                        // Paginated response
                        $eventsData = $response['data']['data'];
                    } elseif (is_array($response['data'])) {
                        // Direct array
                        $eventsData = $response['data'];
                    }
                } elseif (isset($response['events'])) {
                    $eventsData = $response['events'];
                }
                
            } catch (\Exception $e) {
                Log::warning('events/my-events failed, trying /events: ' . $e->getMessage());
                
                // Fallback: try /events with user filter
                try {
                    $response = $this->api->withToken($token)->get('events', $params);
                    Log::info('API Response from /events:', $response);
                    
                    if (isset($response['data'])) {
                        if (isset($response['data']['data'])) {
                            $eventsData = $response['data']['data'];
                        } elseif (is_array($response['data'])) {
                            $eventsData = $response['data'];
                        }
                    }
                    
                    // Filter by current user (organizer)
                    $userId = $user['id'] ?? null;
                    if ($userId) {
                        $eventsData = array_filter($eventsData, function($event) use ($userId) {
                            return isset($event['user_id']) && $event['user_id'] == $userId;
                        });
                        $eventsData = array_values($eventsData);
                    }
                    
                } catch (\Exception $e2) {
                    Log::error('Both endpoints failed: ' . $e2->getMessage());
                }
            }
            
            Log::info('Total events found: ' . count($eventsData));
            
            // Convert arrays to objects
            $eventsData = array_map(function($event) {
                return $this->arrayToObject($event);
            }, $eventsData);

            // Manual pagination
            $perPage = 10;
            $currentPage = $request->get('page', 1);
            $total = count($eventsData);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedData = array_slice($eventsData, $offset, $perPage);

            $events = new LengthAwarePaginator(
                $paginatedData,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            // Get categories for filter
            $categoriesResponse = $this->api->get('categories');
            $categoriesData = $categoriesResponse['data'] ?? [];
            $categories = array_map(fn($cat) => $this->arrayToObject($cat), $categoriesData);

            return view('admin.events.index', compact('events', 'categories'));

        } catch (\Exception $e) {
            Log::error('Events index error: ' . $e->getMessage());
            
            $events = new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]);
            
            return view('admin.events.index', [
                'events' => $events,
                'categories' => [],
                'error' => 'Tidak dapat memuat data events: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new event
     */
    public function create()
    {
        try {
            $categoriesResponse = $this->api->get('categories');
            $categoriesData = $categoriesResponse['data'] ?? [];
            $categories = array_map(fn($cat) => $this->arrayToObject($cat), $categoriesData);

            return view('admin.events.create', compact('categories'));

        } catch (\Exception $e) {
            Log::error('Events create form error: ' . $e->getMessage());
            return redirect()->route('admin.events.index')
                ->with('error', 'Tidak dapat memuat form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'quota' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:draft,published,cancelled,completed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $token = Session::get('api_token');

            if (!$token) {
                return redirect()->route('login')
                    ->with('error', 'Silakan login terlebih dahulu');
            }

            // Prepare data for multipart request
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => (int) $request->category_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'location' => $request->location,
                'event_type' => $request->event_type ?? 'offline',
                'meeting_link' => $request->meeting_link,
                'contact_info' => $request->contact_info,
                'requirements' => $request->requirements,
                'quota' => (int) $request->quota,
                'price' => (float) $request->price,
                'status' => $request->status,
            ];

            Log::info('Creating event with data:', array_keys($data));

            // Use multipart if image is uploaded, otherwise use regular post
            if ($request->hasFile('image')) {
                // Attach the file for multipart upload
                $data['image'] = $request->file('image');
                $response = $this->api->postMultipart('events', $data, $token);
            } else {
                $response = $this->api->withToken($token)->post('events', $data);
            }

            Log::info('Event created response:', $response);

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('admin.events.index')
                    ->with('success', 'Event berhasil dibuat!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal membuat event')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Events store error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified event
     */
    public function show($id)
    {
        try {
            $token = Session::get('api_token');

            $response = $this->api->withToken($token)->get("events/{$id}");
            $eventData = $response['data'] ?? null;

            if (!$eventData) {
                return redirect()->route('admin.events.index')
                    ->with('error', 'Event tidak ditemukan');
            }

            $event = $this->arrayToObject($eventData);

            // Fetch participants data
            try {
                $participantsResponse = $this->api->withToken($token)->get("events/{$id}/participants");
                $participantsData = $participantsResponse['data']['data'] ?? $participantsResponse['data'] ?? [];
            } catch (\Exception $e) {
                $participantsData = [];
            }

            $participants = array_map(fn($p) => $this->arrayToObject($p), $participantsData);

            // Calculate statistics
            $stats = [
                'total' => count($participantsData),
                'attended' => collect($participantsData)->where('status', 'attended')->count(),
                'pending' => collect($participantsData)->where('status', 'registered')->count(),
                'cancelled' => collect($participantsData)->where('status', 'cancelled')->count(),
            ];

            // Calculate attendance rate
            $attendanceRate = $stats['total'] > 0
                ? round(($stats['attended'] / $stats['total']) * 100, 1)
                : 0;

            // Calculate revenue
            $paidParticipants = collect($participantsData)->filter(function($p) {
                return isset($p['is_paid']) && $p['is_paid'] == true;
            });

            $totalRevenue = $paidParticipants->sum(function($p) {
                return $p['amount_paid'] ?? 0;
            });

            $platformFeePercentage = env('PLATFORM_FEE_PERCENTAGE', 5);
            $platformFee = ($totalRevenue * $platformFeePercentage) / 100;
            $organizerEarnings = $totalRevenue - $platformFee;

            $revenue = [
                'total' => $totalRevenue,
                'paid_count' => $paidParticipants->count(),
                'platform_fee' => $platformFee,
                'organizer_earnings' => $organizerEarnings,
            ];

            return view('admin.events.show', compact(
                'event',
                'participants',
                'stats',
                'attendanceRate',
                'revenue'
            ));

        } catch (\Exception $e) {
            Log::error('Events show error: ' . $e->getMessage());
            return redirect()->route('admin.events.index')
                ->with('error', 'Tidak dapat memuat detail event');
        }
    }

    /**
     * Show the form for editing the specified event
     */
    public function edit($id)
    {
        try {
            $token = Session::get('api_token');
            
            $response = $this->api->withToken($token)->get("events/{$id}");
            $eventData = $response['data'] ?? null;

            if (!$eventData) {
                return redirect()->route('admin.events.index')
                    ->with('error', 'Event tidak ditemukan');
            }

            $event = $this->arrayToObject($eventData);

            $categoriesResponse = $this->api->get('categories');
            $categoriesData = $categoriesResponse['data'] ?? [];
            $categories = array_map(fn($cat) => $this->arrayToObject($cat), $categoriesData);

            return view('admin.events.edit', compact('event', 'categories'));

        } catch (\Exception $e) {
            Log::error('Events edit form error: ' . $e->getMessage());
            return redirect()->route('admin.events.index')
                ->with('error', 'Tidak dapat memuat form edit');
        }
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'quota' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:draft,published,cancelled,completed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $token = Session::get('api_token');

            if (!$token) {
                return redirect()->route('login')
                    ->with('error', 'Silakan login terlebih dahulu');
            }

            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => (int) $request->category_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'location' => $request->location,
                'event_type' => $request->event_type ?? 'offline',
                'meeting_link' => $request->meeting_link,
                'contact_info' => $request->contact_info,
                'requirements' => $request->requirements,
                'quota' => (int) $request->quota,
                'price' => (float) $request->price,
                'status' => $request->status,
            ];

            Log::info('Updating event with data:', array_keys($data));

            // Use multipart if image is uploaded, otherwise use regular put
            if ($request->hasFile('image')) {
                // Attach the file for multipart upload
                $data['image'] = $request->file('image');
                $response = $this->api->putMultipart("events/{$id}", $data, $token);
            } else {
                $response = $this->api->withToken($token)->put("events/{$id}", $data);
            }

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('admin.events.index')
                    ->with('success', 'Event berhasil diupdate!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal mengupdate event')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Events update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified event
     */
    public function destroy($id)
    {
        try {
            $token = Session::get('api_token');
            
            if (!$token) {
                return redirect()->route('login')
                    ->with('error', 'Silakan login terlebih dahulu');
            }
            
            $response = $this->api->withToken($token)->delete("events/{$id}");

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('admin.events.index')
                    ->with('success', 'Event berhasil dihapus!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal menghapus event');

        } catch (\Exception $e) {
            Log::error('Events destroy error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Toggle event status
     */
    public function toggleStatus($id)
    {
        try {
            $token = Session::get('api_token');
            
            if (!$token) {
                return redirect()->route('login')
                    ->with('error', 'Silakan login terlebih dahulu');
            }
            
            $eventResponse = $this->api->withToken($token)->get("events/{$id}");
            $event = $eventResponse['data'] ?? null;
            
            if (!$event) {
                return redirect()->back()
                    ->with('error', 'Event tidak ditemukan');
            }
            
            $currentStatus = $event['status'] ?? 'draft';
            $newStatus = match($currentStatus) {
                'draft' => 'published',
                'published' => 'completed',
                'completed' => 'cancelled',
                'cancelled' => 'draft',
                default => 'published'
            };
            
            $updateData = [
                'title' => $event['title'],
                'description' => $event['description'],
                'category_id' => $event['category_id'],
                'start_date' => $event['start_date'],
                'end_date' => $event['end_date'],
                'location' => $event['location'],
                'quota' => $event['quota'],
                'price' => $event['price'],
                'status' => $newStatus,
            ];
            
            $response = $this->api->withToken($token)->put("events/{$id}", $updateData);

            if (isset($response['success']) && $response['success']) {
                return redirect()->back()
                    ->with('success', "Status event berhasil diubah menjadi {$newStatus}!");
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal mengubah status');

        } catch (\Exception $e) {
            Log::error('Events toggle status error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get event participants
     */
    public function participants($id)
    {
        try {
            $token = Session::get('api_token');
            
            $eventResponse = $this->api->withToken($token)->get("events/{$id}");
            $eventData = $eventResponse['data'] ?? null;

            if (!$eventData) {
                return redirect()->route('admin.events.index')
                    ->with('error', 'Event tidak ditemukan');
            }

            $event = $this->arrayToObject($eventData);

            try {
                $participantsResponse = $this->api->withToken($token)->get("events/{$id}/participants");
                $participantsData = $participantsResponse['data']['data'] ?? $participantsResponse['data'] ?? [];
            } catch (\Exception $e) {
                $participantsData = [];
            }
            
            $participants = array_map(fn($p) => $this->arrayToObject($p), $participantsData);

            return view('admin.events.participants', compact('event', 'participants'));

        } catch (\Exception $e) {
            Log::error('Events participants error: ' . $e->getMessage());
            return redirect()->route('admin.events.index')
                ->with('error', 'Tidak dapat memuat data peserta');
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

    public function feedbacks($id)
    {
        try {
            $token = Session::get('api_token');

            // Fetch event details
            $eventResponse = $this->api->withToken($token)->get("events/{$id}");
            $eventData = $eventResponse['data'] ?? null;

            if (!$eventData) {
                return redirect()->route('admin.events.index')
                    ->with('error', 'Event tidak ditemukan');
            }

            $event = $this->arrayToObject($eventData);

            // Fetch detailed summary (includes feedbacks, statistics, and AI summary)
            $feedbacks = [];
            $stats = null;
            $summary = null;

            try {
                $detailedResponse = $this->api->withToken($token)->get("events/{$id}/feedback/summary/detailed");
                $detailedData = $detailedResponse['data'] ?? [];

                // Extract feedbacks
                if (isset($detailedData['feedbacks'])) {
                    $feedbacks = array_map(
                        fn($f) => $this->arrayToObject($f),
                        $detailedData['feedbacks']
                    );
                }

                // Extract statistics
                if (isset($detailedData['statistics'])) {
                    $statsArray = $detailedData['statistics'];
                    $stats = $this->arrayToObject([
                        'average_rating' => $statsArray['average_rating'] ?? 0,
                        'total_feedbacks' => $statsArray['total_feedbacks'] ?? 0,
                        'rating_1' => $statsArray['rating_distribution']['1_star'] ?? 0,
                        'rating_2' => $statsArray['rating_distribution']['2_star'] ?? 0,
                        'rating_3' => $statsArray['rating_distribution']['3_star'] ?? 0,
                        'rating_4' => $statsArray['rating_distribution']['4_star'] ?? 0,
                        'rating_5' => $statsArray['rating_distribution']['5_star'] ?? 0,
                    ]);
                }

                // Extract AI summary
                if (isset($detailedData['summary']) && $detailedData['summary']) {
                    $summary = $this->arrayToObject([
                        'summary' => $detailedData['summary'],
                        'generated_at' => $detailedData['generated_at'] ?? null,
                        'feedback_count' => $detailedData['statistics']['feedback_count_at_summary'] ?? 0,
                    ]);
                }
            } catch (\Exception $e) {
                // If detailed endpoint fails, feedbacks/summary are optional
                Log::info('Detailed feedback fetch failed (event may have no feedbacks yet): ' . $e->getMessage());
            }

            return view('admin.events.feedbacks', compact('event', 'feedbacks', 'stats', 'summary'));

        } catch (\Exception $e) {
            Log::error('Events feedbacks error: ' . $e->getMessage());
            return redirect()->route('admin.events.index')
                ->with('error', 'Tidak dapat memuat feedback event');
        }
    }

    public function generateSummary($id)
    {
        try {
            $token = Session::get('api_token');

            $response = $this->api->withToken($token)->post("events/{$id}/feedback/generate-summary");

            $success = isset($response['success']) ? $response['success'] : false;

            if ($success) {
                return redirect()->route('admin.events.feedbacks', $id)
                    ->with('success', 'AI summary berhasil di-generate!');
            }

            return redirect()->route('admin.events.feedbacks', $id)
                ->with('error', $response['message'] ?? 'Gagal generate summary');

        } catch (\Exception $e) {
            Log::error('Generate summary error: ' . $e->getMessage());
            return redirect()->route('admin.events.feedbacks', $id)
                ->with('error', 'Gagal generate summary: ' . $e->getMessage());
        }
    }
}
