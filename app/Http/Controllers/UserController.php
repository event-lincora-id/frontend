<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackendApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class UserController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Display a listing of users (Event Participants who joined organizer's events)
     */
    public function index(Request $request)
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Get events created by this organizer
            $eventsResponse = $this->api->withToken($token)->get('events/my-events', [
                'per_page' => 1000,
            ]);
            
            Log::info('My Events Response:', ['response' => $eventsResponse]);
            
            // Try different response structures
            $events = $eventsResponse['data']['events'] ?? 
                     $eventsResponse['data']['data'] ?? 
                     $eventsResponse['data'] ?? 
                     $eventsResponse['events'] ?? 
                     [];
            
            Log::info('Total events found:', ['count' => count($events)]);
            
            // Collect all unique participants from all events
            $participantsMap = [];
            
            foreach ($events as $event) {
                $eventId = $event['id'] ?? null;
                if (!$eventId) continue;
                
                try {
                    // Get participants for this event
                    $participantsResponse = $this->api->withToken($token)->get("events/{$eventId}/participants");
                    
                    Log::info("Participants for event {$eventId}:", ['response' => $participantsResponse]);
                    
                    // Try different response structures
                    $eventParticipants = $participantsResponse['data']['participants'] ?? 
                                       $participantsResponse['data']['data'] ?? 
                                       $participantsResponse['data'] ?? 
                                       $participantsResponse['participants'] ?? 
                                       [];
                    
                    if (!is_array($eventParticipants)) {
                        Log::warning("Event participants is not an array for event {$eventId}");
                        continue;
                    }
                    
                    Log::info("Processing {count} participants for event {$eventId}", ['count' => count($eventParticipants)]);
                    
                    foreach ($eventParticipants as $participant) {
                        // Try different possible user ID fields
                        $userId = $participant['user_id'] ?? 
                                 $participant['user']['id'] ?? 
                                 $participant['id'] ?? null;
                        
                        if (!$userId) {
                            Log::warning('No user ID found in participant:', ['participant' => $participant]);
                            continue;
                        }
                        
                        // Initialize participant data if not exists
                        if (!isset($participantsMap[$userId])) {
                            $userName = $participant['user']['name'] ?? 
                                      $participant['name'] ?? 
                                      $participant['user_name'] ?? 
                                      'Unknown';
                            
                            $userEmail = $participant['user']['email'] ?? 
                                       $participant['email'] ?? 
                                       $participant['user_email'] ?? 
                                       '';
                            
                            $participantsMap[$userId] = (object) [
                                'id' => $userId,
                                'name' => $userName,
                                'full_name' => $participant['user']['full_name'] ?? 
                                             $participant['full_name'] ?? 
                                             $userName,
                                'email' => $userEmail,
                                'avatar' => $participant['user']['avatar'] ?? 
                                          $participant['avatar'] ?? null,
                                'role' => $participant['user']['role'] ?? 
                                        $participant['role'] ?? 
                                        'participant',
                                'created_at' => $participant['user']['created_at'] ?? 
                                              $participant['created_at'] ?? 
                                              $participant['registered_at'] ?? 
                                              now(),
                                'events' => [],
                            ];
                        }
                        
                        // Add event participation info
                        $participantsMap[$userId]->events[] = (object) [
                            'event' => (object) [
                                'id' => $event['id'] ?? null,
                                'title' => $event['title'] ?? 'Event',
                                'location' => $event['location'] ?? 'N/A',
                                'start_date' => $event['start_date'] ?? $event['date'] ?? null,
                                'category' => (object) ($event['category'] ?? ['name' => 'Uncategorized']),
                            ],
                            'status' => $participant['status'] ?? 'registered',
                            'is_paid' => $participant['is_paid'] ?? 
                                       ($participant['payment_status'] === 'paid') ?? 
                                       ($participant['payment_status'] === 'completed') ?? 
                                       false,
                            'payment_status' => $participant['payment_status'] ?? null,
                            'attended_at' => $participant['attended_at'] ?? 
                                           $participant['attendance_date'] ?? null,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning("Could not fetch participants for event {$eventId}: " . $e->getMessage());
                    continue;
                }
            }
            
            Log::info('Total unique participants found:', ['count' => count($participantsMap)]);
            
            // Convert to array and apply search filter if needed
            $usersData = array_values($participantsMap);
            
            if ($request->has('search') && $request->search) {
                $search = strtolower($request->search);
                $usersData = array_filter($usersData, function($user) use ($search) {
                    return str_contains(strtolower($user->name), $search) ||
                           str_contains(strtolower($user->email), $search) ||
                           str_contains(strtolower($user->full_name), $search);
                });
                $usersData = array_values($usersData);
            }
            
            // Manual pagination
            $page = $request->get('page', 1);
            $perPage = 10;
            $total = count($usersData);
            $offset = ($page - 1) * $perPage;
            $paginatedData = array_slice($usersData, $offset, $perPage);

            // Create Laravel paginator
            $users = new LengthAwarePaginator(
                $paginatedData,
                $total,
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            return view('admin.users.index', compact('users'));

        } catch (\Exception $e) {
            Log::error('Users index error: ' . $e->getMessage());
            
            $users = new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]);
            
            return view('admin.users.index', [
                'users' => $users,
                'error' => 'Tidak dapat memuat data participants: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,participant,super_admin',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $token = Session::get('api_token');
            
            $data = $request->only(['name', 'full_name', 'email', 'password', 'password_confirmation', 'role', 'phone', 'bio']);

            $response = $this->api->withToken($token)->post('admin/users', $data);

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('admin.users.index')
                    ->with('success', 'User berhasil dibuat!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal membuat user')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Users store error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        try {
            $token = Session::get('api_token');
            
            $response = $this->api->withToken($token)->get("admin/users/{$id}");
            $user = (object) ($response['data'] ?? []);

            if (!isset($user->id)) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'User tidak ditemukan');
            }

            return view('admin.users.show', compact('user'));

        } catch (\Exception $e) {
            Log::error('Users show error: ' . $e->getMessage());
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat memuat detail user');
        }
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        try {
            $token = Session::get('api_token');
            
            $response = $this->api->withToken($token)->get("admin/users/{$id}");
            $user = (object) ($response['data'] ?? []);

            if (!isset($user->id)) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'User tidak ditemukan');
            }

            return view('admin.users.edit', compact('user'));

        } catch (\Exception $e) {
            Log::error('Users edit form error: ' . $e->getMessage());
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat memuat form edit');
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'role' => 'required|in:admin,participant,super_admin',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $token = Session::get('api_token');
            
            $data = $request->only(['name', 'full_name', 'email', 'role', 'phone', 'bio']);
            
            if ($request->filled('password')) {
                $data['password'] = $request->password;
                $data['password_confirmation'] = $request->password_confirmation;
            }

            $response = $this->api->withToken($token)->put("admin/users/{$id}", $data);

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('admin.users.index')
                    ->with('success', 'User berhasil diupdate!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal mengupdate user')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Users update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        try {
            $token = Session::get('api_token');
            $currentUser = Session::get('user');
            
            // Prevent deleting self
            if ($id == $currentUser['id']) {
                return redirect()->back()
                    ->with('error', 'Anda tidak dapat menghapus akun sendiri!');
            }
            
            $response = $this->api->withToken($token)->delete("admin/users/{$id}");

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('admin.users.index')
                    ->with('success', 'User berhasil dihapus!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal menghapus user');

        } catch (\Exception $e) {
            Log::error('Users destroy error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        try {
            $token = Session::get('api_token');
            $currentUser = Session::get('user');
            
            // Prevent toggling self
            if ($id == $currentUser['id']) {
                return redirect()->back()
                    ->with('error', 'Anda tidak dapat mengubah status akun sendiri!');
            }
            
            $response = $this->api->withToken($token)->post("admin/users/{$id}/toggle-status");

            if (isset($response['success']) && $response['success']) {
                return redirect()->back()
                    ->with('success', 'Status user berhasil diubah!');
            }

            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal mengubah status');

        } catch (\Exception $e) {
            Log::error('Users toggle status error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}