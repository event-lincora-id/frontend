<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackendApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Display a listing of categories
     * GET /categories - Get all active categories
     */
    public function index(Request $request)
    {
        try {
            // Get categories from API
            $response = $this->api->get('categories');

            // API returns flat array in data, not paginated
            $categoriesData = $response['data'] ?? [];

            // Build events count per category (for current admin's events only)
            $eventsCountByCategory = [];
            try {
                $token = \Illuminate\Support\Facades\Session::get('api_token');
                $user = \Illuminate\Support\Facades\Session::get('user');
                if ($token) {
                    // Prefer organizer-specific endpoint to avoid counting other organizers' events
                    try {
                        $eventsResp = $this->api->withToken($token)->get('events/my-events', ['per_page' => 1000]);
                        $eventsList = $eventsResp['data']['data'] ?? ($eventsResp['data'] ?? []);
                    } catch (\Exception $e) {
                        // Fallback: use generic events endpoint, then filter by current user's id
                        $fallbackResp = $this->api->withToken($token)->get('events', ['per_page' => 1000]);
                        $tmpList = $fallbackResp['data']['data'] ?? ($fallbackResp['data'] ?? []);
                        $userId = $user['id'] ?? null;
                        $eventsList = array_values(array_filter($tmpList, function ($evt) use ($userId) {
                            return isset($evt['user_id']) && $userId && $evt['user_id'] == $userId;
                        }));
                    }

                    if (is_array($eventsList)) {
                        foreach ($eventsList as $evt) {
                            $catId = $evt['category_id'] ?? ($evt['category']['id'] ?? null);
                            if ($catId) {
                                $eventsCountByCategory[$catId] = ($eventsCountByCategory[$catId] ?? 0) + 1;
                            }
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
                $categoriesData = array_values($categoriesData); // Re-index array
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

            return view('admin.categories.index', compact('categories'));

        } catch (\Exception $e) {
            Log::error('Categories index error: ' . $e->getMessage());
            
            $categories = new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]);
            
            return view('admin.categories.index', [
                'categories' => $categories,
                'error' => 'Tidak dapat memuat data kategori: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category
     * POST /categories (Auth Required, Admin Only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7', // Hex color code like #3B82F6
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
                return redirect()->route('admin.categories.index')
                    ->with('success', 'Kategori berhasil dibuat!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal membuat kategori')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Categories store error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        try {
            $response = $this->api->get('categories');
            $categories = $response['data'] ?? [];
            
            // Find category by ID
            $category = null;
            foreach ($categories as $cat) {
                if ($cat['id'] == $id) {
                    $category = $cat;
                    break;
                }
            }

            if (!$category) {
                return redirect()->route('admin.categories.index')
                    ->with('error', 'Kategori tidak ditemukan');
            }

            return view('admin.categories.show', compact('category'));

        } catch (\Exception $e) {
            Log::error('Categories show error: ' . $e->getMessage());
            return redirect()->route('admin.categories.index')
                ->with('error', 'Tidak dapat memuat detail kategori');
        }
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit($id)
    {
        try {
            $response = $this->api->get('categories');
            $categories = $response['data'] ?? [];
            
            // Find category by ID
            $category = null;
            foreach ($categories as $cat) {
                if ($cat['id'] == $id) {
                    $category = $cat;
                    break;
                }
            }

            if (!$category) {
                return redirect()->route('admin.categories.index')
                    ->with('error', 'Kategori tidak ditemukan');
            }

            return view('admin.categories.edit', compact('category'));

        } catch (\Exception $e) {
            Log::error('Categories edit form error: ' . $e->getMessage());
            return redirect()->route('admin.categories.index')
                ->with('error', 'Tidak dapat memuat form edit');
        }
    }

    /**
     * Update the specified category
     * PUT /categories/{id} (Auth Required, Admin Only)
     */
    public function update(Request $request, $id)
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
                return redirect()->route('admin.categories.index')
                    ->with('success', 'Kategori berhasil diupdate!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal mengupdate kategori')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Categories update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified category
     * DELETE /categories/{id} (Auth Required, Admin Only)
     */
    public function destroy($id)
    {
        try {
            $token = Session::get('api_token');
            
            if (!$token) {
                return redirect()->route('login')
                    ->with('error', 'Silakan login terlebih dahulu');
            }
            
            $response = $this->api->withToken($token)->delete("categories/{$id}");

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('admin.categories.index')
                    ->with('success', 'Kategori berhasil dihapus!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal menghapus kategori');

        } catch (\Exception $e) {
            Log::error('Categories destroy error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Toggle category status (if API supports this)
     * This might need a custom endpoint in the backend
     */
    public function toggleStatus($id)
    {
        try {
            $token = Session::get('api_token');
            
            if (!$token) {
                return redirect()->route('login')
                    ->with('error', 'Silakan login terlebih dahulu');
            }
            
            // Get current category data
            $response = $this->api->get('categories');
            $categories = $response['data'] ?? [];
            
            $category = null;
            foreach ($categories as $cat) {
                if ($cat['id'] == $id) {
                    $category = $cat;
                    break;
                }
            }

            if (!$category) {
                return redirect()->back()
                    ->with('error', 'Kategori tidak ditemukan');
            }

            // Toggle is_active status
            $newStatus = !($category['is_active'] ?? true);
            
            $data = [
                'name' => $category['name'],
                'description' => $category['description'] ?? '',
                'color' => $category['color'] ?? '#3B82F6',
                'is_active' => $newStatus,
            ];

            $updateResponse = $this->api->withToken($token)->put("categories/{$id}", $data);

            if (isset($updateResponse['success']) && $updateResponse['success']) {
                $status = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
                return redirect()->back()
                    ->with('success', "Kategori berhasil {$status}!");
            }

            return redirect()->back()
                ->with('error', $updateResponse['message'] ?? 'Gagal mengubah status');

        } catch (\Exception $e) {
            Log::error('Categories toggle status error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get category statistics
     * This might need to be implemented based on available API endpoints
     */
    public function statistics($id)
    {
        try {
            $token = Session::get('api_token');
            
            // Get category info
            $response = $this->api->get('categories');
            $categories = $response['data'] ?? [];
            
            $category = null;
            foreach ($categories as $cat) {
                if ($cat['id'] == $id) {
                    $category = $cat;
                    break;
                }
            }

            if (!$category) {
                return redirect()->route('admin.categories.index')
                    ->with('error', 'Kategori tidak ditemukan');
            }

            // Try to get events for this category
            $eventsResponse = $this->api->withToken($token)->get('admin/events', ['category_id' => $id]);
            $events = $eventsResponse['data']['data'] ?? [];

            $statistics = [
                'category' => $category,
                'total_events' => count($events),
                'events' => $events,
            ];

            return view('admin.categories.statistics', compact('statistics', 'category'));

        } catch (\Exception $e) {
            Log::error('Categories statistics error: ' . $e->getMessage());
            return redirect()->route('admin.categories.index')
                ->with('error', 'Tidak dapat memuat statistik');
        }
    }
}