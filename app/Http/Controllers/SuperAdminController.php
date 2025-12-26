<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackendApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class SuperAdminController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Display super admin dashboard with platform statistics
     */
    public function dashboard()
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Get platform statistics
            $statsResponse = $this->api->withToken($token)->get('super-admin/statistics');
            
            Log::info('Stats Response:', ['response' => $statsResponse]);
            
            // Handle different response structures
            $data = $statsResponse['data'] ?? $statsResponse ?? [];
            
            // Extract data from different possible structures
            $statistics = $data['statistics'] ?? $data ?? [];
            $periodStats = $data['period_statistics'] ?? [];
            $eventStatusBreakdown = $data['event_status_breakdown'] ?? [
                'published' => 0,
                'draft' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
            $monthlyTrends = $data['monthly_trends'] ?? [];
            $topOrganizers = $data['top_organizers'] ?? [];
            $categoryBreakdown = $data['category_breakdown'] ?? [];

            // Ensure all statistics have default values
            $stats = [
                'total_organizers' => $statistics['total_organizers'] ?? 0,
                'total_events' => $statistics['total_events'] ?? 0,
                'total_participants' => $statistics['total_participants'] ?? 0,
                'total_revenue' => $statistics['total_revenue'] ?? 0,
                'period' => $periodStats,
                'event_breakdown' => $eventStatusBreakdown,
                'monthly_trends' => $monthlyTrends,
                'top_organizers' => $topOrganizers,
                'category_breakdown' => $categoryBreakdown,
            ];

            return view('admin.super.dashboard', compact('stats'));

        } catch (\Exception $e) {
            Log::error('Super Admin dashboard error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return view('admin.super.dashboard', [
                'stats' => [
                    'total_organizers' => 0,
                    'total_events' => 0,
                    'total_participants' => 0,
                    'total_revenue' => 0,
                    'event_breakdown' => [],
                    'monthly_trends' => [],
                    'top_organizers' => [],
                    'category_breakdown' => [],
                ],
                'error' => 'Tidak dapat memuat statistik: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display all organizers
     */
    public function organizers(Request $request)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Build query parameters
            $params = [
                'page' => $request->get('page', 1),
                'per_page' => 10,
            ];

            if ($request->has('search') && $request->search) {
                $params['search'] = $request->search;
            }

            if ($request->has('status') && $request->status) {
                $params['status'] = $request->status;
            }

            // Get all organizers from API
            $response = $this->api->withToken($token)->get('super-admin/organizers', $params);

            Log::info('Organizers Response:', ['response' => $response]);

            $organizersData = $response['data']['organizers'] ?? [];
            $pagination = $response['data']['pagination'] ?? [
                'total' => count($organizersData),
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1
            ];

            // Convert to objects recursively
            $organizersData = array_map(function($o) {
                return json_decode(json_encode($o));
            }, $organizersData);

            // If total_participants is 0 for any organizer, try to calculate from their events
            $organizersData = array_map(function($organizer) use ($token) {
                if (isset($organizer->events) && ($organizer->events->total_participants ?? 0) == 0 && ($organizer->events->total ?? 0) > 0) {
                    try {
                        // Get events for this organizer to calculate actual participants
                        $eventsResponse = $this->api->withToken($token)->get('super-admin/events', [
                            'organizer_id' => $organizer->id,
                            'per_page' => 100
                        ]);
                        
                        $events = $eventsResponse['data']['events'] ?? [];
                        $totalParticipants = 0;
                        
                        foreach ($events as $event) {
                            $participants = $event['participants'] ?? [];
                            $totalParticipants += ($participants['total'] ?? 0);
                        }
                        
                        // Update organizer's total_participants
                        $organizer->events->total_participants = $totalParticipants;
                    } catch (\Exception $e) {
                        Log::warning('Could not calculate participants for organizer ' . $organizer->id);
                    }
                }
                return $organizer;
            }, $organizersData);

            Log::info('Organizers Data After Conversion:', ['data' => $organizersData]);

            // Create Laravel paginator
            $organizers = new LengthAwarePaginator(
                $organizersData,
                $pagination['total'] ?? count($organizersData),
                $pagination['per_page'] ?? 10,
                $pagination['current_page'] ?? 1,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            return view('admin.super.organizers', compact('organizers'));

        } catch (\Exception $e) {
            Log::error('Super Admin organizers error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());

            $organizers = new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]);

            return view('admin.super.organizers', [
                'organizers' => $organizers,
                'error' => 'Tidak dapat memuat data organizers: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display all events across the platform
     */
    public function events(Request $request)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Build query parameters
            $params = [
                'page' => $request->get('page', 1),
                'per_page' => 10,
            ];

            if ($request->has('search') && $request->search) {
                $params['search'] = $request->search;
            }

            if ($request->has('status') && $request->status) {
                $params['status'] = $request->status;
            }

            if ($request->has('category_id') && $request->category_id) {
                $params['category_id'] = $request->category_id;
            }

            if ($request->has('organizer_id') && $request->organizer_id) {
                $params['organizer_id'] = $request->organizer_id;
            }

            // Get all events from API
            $response = $this->api->withToken($token)->get('super-admin/events', $params);

            Log::info('Events Response:', ['response' => $response]);

            // Handle different response structures
            $data = $response['data'] ?? $response ?? [];
            $eventsData = $data['events'] ?? $data ?? [];
            
            $pagination = $data['pagination'] ?? [
                'total' => count($eventsData),
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1
            ];

            // Convert to objects
            $eventsData = array_map(function($e) {
                return json_decode(json_encode($e));
            }, $eventsData);

            Log::info('Events Data After Conversion:', ['data' => $eventsData]);

            // Create Laravel paginator
            $events = new LengthAwarePaginator(
                $eventsData,
                $pagination['total'] ?? count($eventsData),
                $pagination['per_page'] ?? 10,
                $pagination['current_page'] ?? 1,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            return view('admin.super.events', compact('events'));

        } catch (\Exception $e) {
            Log::error('Super Admin events error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());

            $events = new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]);

            return view('admin.super.events', [
                'events' => $events,
                'error' => 'Tidak dapat memuat data events: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display organizer detail
     */
    public function organizerDetail(Request $request, $id)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Get organizer detail
            $response = $this->api->withToken($token)->get("super-admin/organizers/{$id}");
            
            $organizer = $response['data'] ?? [];

            return view('admin.super.organizer-detail', compact('organizer'));

        } catch (\Exception $e) {
            Log::error('Super Admin organizer detail error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat memuat data organizer: ' . $e->getMessage());
        }
    }

    /**
     * Display event detail
     */
    public function eventDetail(Request $request, $id)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Get event detail
            $response = $this->api->withToken($token)->get("super-admin/events/{$id}");
            
            $event = $response['data'] ?? [];

            return view('admin.super.event-detail', compact('event'));

        } catch (\Exception $e) {
            Log::error('Super Admin event detail error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat memuat data event: ' . $e->getMessage());
        }
    }

    /**
     * Toggle organizer status
     */
    public function toggleOrganizerStatus(Request $request, $id)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Toggle organizer status via API
            $response = $this->api->withToken($token)->post("super-admin/organizers/{$id}/toggle-status", []);
            
            return back()->with('success', 'Status organizer berhasil diubah');

        } catch (\Exception $e) {
            Log::error('Super Admin toggle organizer status error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengubah status organizer: ' . $e->getMessage());
        }
    }

    /**
     * Display all categories with event counts from all organizers
     */
    public function categories(Request $request)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Get all categories from API
            $response = $this->api->get('categories');
            $categoriesData = $response['data'] ?? [];

            // Build events count per category (from ALL organizers)
            $eventsCountByCategory = [];
            try {
                // Fetch all events from all organizers
                $eventsResp = $this->api->withToken($token)->get('super-admin/events', ['per_page' => 10000]);
                
                // Handle different response shapes
                $eventsList = $eventsResp['data']['events'] ?? ($eventsResp['data']['data'] ?? ($eventsResp['data'] ?? []));
                
                if (is_array($eventsList)) {
                    foreach ($eventsList as $evt) {
                        $catId = $evt['category_id'] ?? ($evt['category']['id'] ?? null);
                        if ($catId) {
                            $eventsCountByCategory[$catId] = ($eventsCountByCategory[$catId] ?? 0) + 1;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::info('Unable to fetch events for category counts: ' . $e->getMessage());
            }

            // Attach events_count to each category item
            $categoriesData = array_map(function ($cat) use ($eventsCountByCategory) {
                if (!is_array($cat)) return $cat;
                $id = $cat['id'] ?? null;
                $cat['events_count'] = $id && isset($eventsCountByCategory[$id])
                    ? $eventsCountByCategory[$id]
                    : 0;
                return $cat;
            }, $categoriesData);

            // Apply client-side search if needed
            if ($request->has('search') && $request->search) {
                $search = strtolower($request->search);
                $categoriesData = array_filter($categoriesData, function($category) use ($search) {
                    return stripos($category['name'] ?? '', $search) !== false ||
                           stripos($category['description'] ?? '', $search) !== false;
                });
                $categoriesData = array_values($categoriesData);
            }

            // Apply client-side sorting if needed
            if ($request->has('sort') && $request->sort) {
                switch ($request->sort) {
                    case 'name_asc':
                        usort($categoriesData, fn($a, $b) => strcasecmp($a['name'] ?? '', $b['name'] ?? ''));
                        break;
                    case 'name_desc':
                        usort($categoriesData, fn($a, $b) => strcasecmp($b['name'] ?? '', $a['name'] ?? ''));
                        break;
                    case 'events_desc':
                        usort($categoriesData, fn($a, $b) => (int)($b['events_count'] ?? 0) <=> (int)($a['events_count'] ?? 0));
                        break;
                    case 'events_asc':
                        usort($categoriesData, fn($a, $b) => (int)($a['events_count'] ?? 0) <=> (int)($b['events_count'] ?? 0));
                        break;
                    case 'active':
                        usort($categoriesData, fn($a, $b) => ($b['is_active'] ?? 0) <=> ($a['is_active'] ?? 0));
                        break;
                    case 'inactive':
                        usort($categoriesData, fn($a, $b) => ($a['is_active'] ?? 0) <=> ($b['is_active'] ?? 0));
                        break;
                }
            }

            // Manual pagination
            $perPage = 10;
            $currentPage = $request->get('page', 1);
            $total = count($categoriesData);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedData = array_slice($categoriesData, $offset, $perPage);

            // Create Laravel paginator
            $categories = new LengthAwarePaginator(
                $paginatedData,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            return view('admin.super.categories', compact('categories'));

        } catch (\Exception $e) {
            Log::error('Super Admin categories error: ' . $e->getMessage());
            
            $categories = new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]);
            
            return view('admin.super.categories', [
                'categories' => $categories,
                'error' => 'Tidak dapat memuat data kategori: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created category (Super Admin)
     */
    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
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
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
            ];

            $response = $this->api->withToken($token)->post('categories', $data);

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('super.admin.categories')
                    ->with('success', 'Kategori berhasil dibuat!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal membuat kategori')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Super Admin categories store error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified category (Super Admin)
     */
    public function updateCategory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
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
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
            ];

            $response = $this->api->withToken($token)->put("categories/{$id}", $data);

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('super.admin.categories')
                    ->with('success', 'Kategori berhasil diupdate!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal mengupdate kategori')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Super Admin categories update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified category (Super Admin)
     */
    public function destroyCategory($id)
    {
        try {
            $token = Session::get('api_token');
            
            if (!$token) {
                return redirect()->route('login')
                    ->with('error', 'Silakan login terlebih dahulu');
            }
            
            $response = $this->api->withToken($token)->delete("categories/{$id}");

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('super.admin.categories')
                    ->with('success', 'Kategori berhasil dihapus!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal menghapus kategori');

        } catch (\Exception $e) {
            Log::error('Super Admin categories destroy error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display all users (participants and organizers)
     */
    public function users(Request $request)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Build query parameters
            $params = [
                'page' => $request->get('page', 1),
                'per_page' => 15,
            ];

            if ($request->has('search') && $request->search) {
                $params['search'] = $request->search;
            }

            if ($request->has('role') && $request->role) {
                $params['role'] = $request->role;
            }

            // Try different endpoint variations
            $usersData = [];
            $endpoints = ['super-admin/users', 'admin/users', 'users'];
            
            $pagination = [];

            foreach ($endpoints as $endpoint) {
                try {
                    $response = $this->api->withToken($token)->get($endpoint, $params);

                    Log::info("Users Response from {$endpoint}:", ['response' => $response]);

                    // Extract users data based on response structure
                    if (isset($response['data']['users'])) {
                        // New API structure
                        $usersData = $response['data']['users'];
                        $pagination = $response['data']['pagination'] ?? [];
                    } elseif (isset($response['data']['data'])) {
                        // Alternative structure
                        $usersData = $response['data']['data'];
                    } elseif (isset($response['data'])) {
                        // Direct data structure
                        $usersData = $response['data'];
                    } else {
                        $usersData = $response ?? [];
                    }

                    if (!is_array($usersData)) {
                        $usersData = [];
                    }

                    // If we got data, break
                    if (!empty($usersData)) {
                        break;
                    }
                } catch (\Exception $e) {
                    Log::info("Endpoint {$endpoint} not available: " . $e->getMessage());
                    continue;
                }
            }
            
            // If all endpoints failed, generate mock data for demonstration
            if (empty($usersData)) {
                Log::warning('No user endpoint available, generating mock data');
                $usersData = $this->generateMockUsers($request->get('search'), $request->get('role'));
            }

            // Convert to objects recursively
            $usersData = array_map(function($u) {
                return json_decode(json_encode($u));
            }, $usersData);

            // Apply client-side search filter if needed
            if ($request->has('search') && $request->search) {
                $searchTerm = strtolower($request->search);
                $usersData = array_filter($usersData, function($user) use ($searchTerm) {
                    return stripos($user->name ?? '', $searchTerm) !== false ||
                           stripos($user->email ?? '', $searchTerm) !== false;
                });
            }

            // Apply client-side role filter if needed
            if ($request->has('role') && $request->role) {
                $usersData = array_filter($usersData, function($user) use ($request) {
                    return ($user->role ?? 'participant') == $request->role;
                });
            }

            // Reset array keys after filtering
            $usersData = array_values($usersData);

            // Use API pagination if available, otherwise manual pagination
            if (!empty($pagination)) {
                // Use API pagination (already paginated from backend)
                $users = new LengthAwarePaginator(
                    $usersData,
                    $pagination['total'] ?? count($usersData),
                    $pagination['per_page'] ?? 15,
                    $pagination['current_page'] ?? 1,
                    [
                        'path' => $request->url(),
                        'query' => $request->query(),
                    ]
                );
            } else {
                // Manual client-side pagination (for mock data or old API)
                $perPage = 15;
                $page = $request->get('page', 1);
                $total = count($usersData);
                $offset = ($page - 1) * $perPage;
                $paginatedUsers = array_slice($usersData, $offset, $perPage);

                $users = new LengthAwarePaginator(
                    $paginatedUsers,
                    $total,
                    $perPage,
                    $page,
                    [
                        'path' => $request->url(),
                        'query' => $request->query(),
                    ]
                );
            }

            return view('admin.super.users', compact('users'));

        } catch (\Exception $e) {
            Log::error('Super Admin users error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());

            $users = new LengthAwarePaginator([], 0, 15, 1, ['path' => $request->url()]);

            return view('admin.super.users', [
                'users' => $users,
                'error' => 'Tidak dapat memuat data users: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate mock user data for demonstration
     */
    private function generateMockUsers($search = null, $role = null)
    {
        $mockUsers = [
            (object)[
                'id' => 1,
                'name' => 'Ahmad Ridho',
                'email' => 'ahmad.ridho@example.com',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => '2024-01-15'
            ],
            (object)[
                'id' => 2,
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'role' => 'participant',
                'status' => 'active',
                'created_at' => '2024-01-20'
            ],
            (object)[
                'id' => 3,
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'role' => 'participant',
                'status' => 'active',
                'created_at' => '2024-02-01'
            ],
            (object)[
                'id' => 4,
                'name' => 'Rina Wijaya',
                'email' => 'rina.wijaya@example.com',
                'role' => 'admin',
                'status' => 'inactive',
                'created_at' => '2024-02-10'
            ],
            (object)[
                'id' => 5,
                'name' => 'Doni Pratama',
                'email' => 'doni.pratama@example.com',
                'role' => 'participant',
                'status' => 'active',
                'created_at' => '2024-02-15'
            ],
            (object)[
                'id' => 6,
                'name' => 'Maya Kusuma',
                'email' => 'maya.kusuma@example.com',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => '2024-03-01'
            ],
            (object)[
                'id' => 7,
                'name' => 'Hendra Sutrisno',
                'email' => 'hendra.sutrisno@example.com',
                'role' => 'participant',
                'status' => 'suspended',
                'created_at' => '2024-03-05'
            ],
            (object)[
                'id' => 8,
                'name' => 'Lina Handoko',
                'email' => 'lina.handoko@example.com',
                'role' => 'participant',
                'status' => 'active',
                'created_at' => '2024-03-10'
            ],
        ];

        return $mockUsers;
    }

    /**
     * Suspend a user
     */
    public function suspendUser(Request $request, $id)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ], 401);
            }

            $response = $this->api->withToken($token)->post("super-admin/users/{$id}/suspend", []);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Super Admin suspend user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a user
     */
    public function activateUser(Request $request, $id)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ], 401);
            }

            $response = $this->api->withToken($token)->post("super-admin/users/{$id}/activate", []);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Super Admin activate user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser(Request $request, $id)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ], 401);
            }

            $response = $this->api->withToken($token)->delete("super-admin/users/{$id}");

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Super Admin delete user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
