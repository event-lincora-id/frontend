<?php

namespace App\Http\Controllers;

use App\Services\BackendApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ParticipantDashboardController extends Controller
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

            // Convert user to object if it's an array
            if (is_array($user)) {
                $user = (object) $user;
            }

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Get user's registered events from API
            try {
                // Request all participations (high per_page to avoid pagination)
                $participationsResponse = $this->api->withToken($token)->get('participants/my-participations', [
                    'per_page' => 100
                ]);

                Log::info('Participations API response:', [
                    'has_success' => isset($participationsResponse['success']),
                    'success_value' => $participationsResponse['success'] ?? null,
                    'has_data' => isset($participationsResponse['data']),
                    'data_count' => is_array($participationsResponse['data'] ?? null) ? count($participationsResponse['data']) : 'not array',
                    'first_item_raw' => isset($participationsResponse['data'][0]) ? json_encode($participationsResponse['data'][0]) : 'no first item',
                    'data_keys' => isset($participationsResponse['data']) ? array_keys($participationsResponse['data']) : []
                ]);

                // Parse response - handle paginated response
                $participationsData = [];
                if (isset($participationsResponse['success']) && $participationsResponse['success']) {
                    $data = $participationsResponse['data'] ?? [];

                    // Check if response is paginated (has 'data' key with pagination metadata)
                    if (isset($data['data']) && is_array($data['data'])) {
                        // Extract actual data from paginated response
                        $participationsData = $data['data'];
                    } else {
                        // Direct array response
                        $participationsData = $data;
                    }
                } elseif (isset($participationsResponse['data'])) {
                    $data = $participationsResponse['data'];

                    // Check if paginated
                    if (isset($data['data']) && is_array($data['data'])) {
                        $participationsData = $data['data'];
                    } else {
                        $participationsData = $data;
                    }
                } else {
                    // Fallback: maybe the response is the array itself
                    $participationsData = is_array($participationsResponse) ? $participationsResponse : [];
                }

                Log::info('Participations data extracted:', [
                    'count' => count($participationsData),
                    'first_item_type' => isset($participationsData[0]) ? gettype($participationsData[0]) : 'no first item',
                    'first_item' => isset($participationsData[0]) ? json_encode($participationsData[0]) : 'none'
                ]);

                $registeredEvents = collect($participationsData)->map(function($item) {
                    // Convert to object recursively
                    if (is_array($item)) {
                        $item = (object) $item;
                    }
                    // Convert event nested object
                    if (isset($item->event) && is_array($item->event)) {
                        $item->event = (object) $item->event;
                    }
                    return $item;
                });

                Log::info('After mapping:', [
                    'registered_count' => $registeredEvents->count(),
                    'first_item_has_event' => $registeredEvents->isNotEmpty() ? isset($registeredEvents->first()->event) : false,
                ]);
            } catch (\Exception $e) {
                Log::warning('Could not fetch participations: ' . $e->getMessage());
                $registeredEvents = collect([]);
            }

            // Try to get bookmarked events (if endpoint exists)
            $bookmarks = collect([]);
            try {
                $bookmarksResponse = $this->api->withToken($token)->get('bookmarks');
                $bookmarksData = [];
                if (isset($bookmarksResponse['success']) && $bookmarksResponse['success']) {
                    $bookmarksData = $bookmarksResponse['data'] ?? [];
                } elseif (isset($bookmarksResponse['data'])) {
                    $bookmarksData = $bookmarksResponse['data'];
                }
                $bookmarks = collect($bookmarksData);
            } catch (\Exception $e) {
                // Bookmarks endpoint doesn't exist yet - that's ok
                Log::info('Bookmarks endpoint not available: ' . $e->getMessage());
            }

            // Get payment history (3 most recent)
            $paymentHistory = collect([]);
            try {
                $paymentResponse = $this->api->withToken($token)->get('payments/history', ['per_page' => 3]);

                if (isset($paymentResponse['success']) && $paymentResponse['success']) {
                    $paymentData = $paymentResponse['data']['payments'] ?? [];
                    $paymentHistory = collect($paymentData)->map(function($item) {
                        if (is_array($item)) {
                            $item = (object) $item;
                        }
                        // Convert nested objects
                        if (isset($item->event) && is_array($item->event)) {
                            $item->event = (object) $item->event;
                        }
                        if (isset($item->payment) && is_array($item->payment)) {
                            $item->payment = (object) $item->payment;
                        }
                        if (isset($item->participation) && is_array($item->participation)) {
                            $item->participation = (object) $item->participation;
                        }
                        return $item;
                    })->take(3);
                }
            } catch (\Exception $e) {
                Log::info('Payment history endpoint not available: ' . $e->getMessage());
            }

            // Debug: Log sample event data structure
            if ($registeredEvents->isNotEmpty()) {
                $firstEvent = $registeredEvents->first();
                Log::info('Sample event data:', [
                    'event_exists' => isset($firstEvent->event),
                    'event_type' => isset($firstEvent->event) ? gettype($firstEvent->event) : 'null',
                    'start_date_exists' => isset($firstEvent->event) && isset($firstEvent->event->start_date),
                    'start_date_value' => $firstEvent->event->start_date ?? 'N/A',
                    'full_structure' => json_encode($firstEvent)
                ]);
            }

            // Get upcoming events
            $upcomingEvents = $registeredEvents
                ->filter(function ($participant) {
                    return isset($participant->event->start_date)
                        && \Carbon\Carbon::parse($participant->event->start_date)->isAfter(now());
                })
                ->sortBy(function ($participant) {
                    return $participant->event->start_date ?? now();
                })
                ->values();

            // Get past events
            $pastEvents = $registeredEvents
                ->filter(function ($participant) {
                    return isset($participant->event->start_date)
                        && \Carbon\Carbon::parse($participant->event->start_date)->isBefore(now());
                })
                ->sortByDesc(function ($participant) {
                    return $participant->event->start_date ?? now();
                })
                ->values();

            // Get user statistics
            $stats = [
                'total_registered' => $registeredEvents->count(), // Count all registered events
                'attended_events' => $registeredEvents->where('status', 'attended')->count(),
                'upcoming_events' => $upcomingEvents->count(),
            ];

            Log::info('Dashboard loaded:', [
                'user_id' => $user->id ?? 'unknown',
                'registered_events' => $registeredEvents->count(),
                'upcoming_events' => $upcomingEvents->count(),
                'past_events' => $pastEvents->count(),
                'current_time' => now()->toDateTimeString()
            ]);

            return view('participant.dashboard', compact('upcomingEvents', 'pastEvents', 'stats', 'paymentHistory'));
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return view('participant.dashboard', [
                'upcomingEvents' => collect([]),
                'pastEvents' => collect([]),
                'stats' => ['total_registered' => 0, 'attended_events' => 0, 'upcoming_events' => 0],
                'paymentHistory' => collect([]),
                'error' => 'Tidak dapat memuat data dari server'
            ]);
        }
    }

    public function profile()
    {
        try {
            $token = Session::get('api_token');
            $user = Session::get('user');
            
            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Fetch fresh profile data from API
            $profileResponse = $this->api->withToken($token)->get('profile');
            
            if (isset($profileResponse['success']) && $profileResponse['success']) {
                $user = $profileResponse['data'] ?? $user;
                // Update session with fresh data
                Session::put('user', $user);
            }

            return view('participant.profile.index', compact('user'));
        } catch (\Exception $e) {
            Log::error('Profile fetch error: ' . $e->getMessage());
            $user = Session::get('user');
            return view('participant.profile.index', compact('user'));
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $token = Session::get('api_token');
            
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'bio' => 'nullable|string|max:1000',
            ]);

            $response = $this->api->withToken($token)->put('profile', $validated);

            if (isset($response['success']) && $response['success']) {
                // Update session data
                $updatedUser = $response['data'] ?? Session::get('user');
                Session::put('user', $updatedUser);
                
                return redirect()->route('participant.profile')
                    ->with('success', 'Profile berhasil diperbarui!');
            }

            return back()->withErrors(['error' => 'Gagal memperbarui profile']);
        } catch (\Exception $e) {
            Log::error('Update profile error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Tidak dapat memperbarui profile. Silakan coba lagi.']);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $token = Session::get('api_token');
            
            $validated = $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $response = $this->api->withToken($token)->post('profile/change-password', [
                'current_password' => $validated['current_password'],
                'password' => $validated['password'],
                'password_confirmation' => $validated['password_confirmation'],
            ]);

            if (isset($response['success']) && $response['success']) {
                return redirect()->route('participant.profile')
                    ->with('success', 'Password berhasil diubah!');
            }

            return back()->withErrors(['error' => $response['message'] ?? 'Gagal mengubah password']);
        } catch (\Exception $e) {
            Log::error('Change password error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Tidak dapat mengubah password. Periksa password lama Anda.']);
        }
    }
}