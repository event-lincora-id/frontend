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

            if (!$token || !$user) {
                return redirect()->route('login');
            }

            // Get user's registered events from API
            try {
                $participationsResponse = $this->api->withToken($token)->get('participants/my-participations');
                
                // Parse response - handle both direct array and wrapped response
                $participationsData = [];
                if (isset($participationsResponse['success']) && $participationsResponse['success']) {
                    $participationsData = $participationsResponse['data'] ?? [];
                } elseif (isset($participationsResponse['data'])) {
                    $participationsData = $participationsResponse['data'];
                }
                
                $registeredEvents = collect($participationsData)->map(function($item) {
                    if (is_array($item)) {
                        $item = (object) $item;
                    }
                    if (isset($item->event) && is_array($item->event)) {
                        $item->event = (object) $item->event;
                    }
                    return $item;
                });
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

            // Get upcoming events
            $upcomingEvents = $registeredEvents
                ->filter(function ($participant) {
                    return isset($participant->event->start_date) 
                        && \Carbon\Carbon::parse($participant->event->start_date)->isAfter(now());
                })
                ->sortBy(function ($participant) {
                    return $participant->event->start_date ?? now();
                })
                ->take(5)
                ->values();

            // Get user statistics - only count active/upcoming events
            $stats = [
                'total_registered' => $upcomingEvents->count(), // Only count upcoming events
                'attended_events' => $registeredEvents->where('status', 'attended')->count(),
                'upcoming_events' => $upcomingEvents->count(),
            ];

            Log::info('Dashboard loaded:', [
                'user_id' => $user->id ?? 'unknown',
                'registered_events' => $registeredEvents->count(),
                'upcoming_events' => $upcomingEvents->count()
            ]);

            return view('participant.dashboard', compact('upcomingEvents', 'stats'));
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return view('participant.dashboard', [
                'upcomingEvents' => collect([]),
                'stats' => ['total_registered' => 0, 'attended_events' => 0, 'upcoming_events' => 0],
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