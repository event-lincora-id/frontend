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

    public function index(Request $request)
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

            // Get date range parameter (default: 30 days)
            $dateRange = $request->get('date_range', '30');

            // Get dashboard data from Backend API
            $dashboardData = $this->getDashboardData($token);

            // Get analytics data from Backend API
            $analyticsData = $this->getAnalyticsData($token, $dateRange);

            // Get balance/finance data from Backend API
            $balanceData = $this->getBalanceData($token);

            // Merge all data for the view
            $data = $this->mergeData($dashboardData, $analyticsData, $balanceData, $dateRange);

            return view('admin.dashboard', $data);

        } catch (\Exception $e) {
            Log::error('Admin Dashboard error: ' . $e->getMessage());

            // Return view with empty/default data
            return view('admin.dashboard', $this->getEmptyData($e->getMessage()));
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
    
    /**
     * Get dashboard data from Backend API
     */
    private function getDashboardData($token)
    {
        try {
            $response = $this->api->withToken($token)->get('admin/dashboard');
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            Log::warning('Failed to get dashboard data from API: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get analytics data from Backend API
     */
    private function getAnalyticsData($token, $dateRange)
    {
        try {
            $response = $this->api->withToken($token)->get('analytics', [
                'date_range' => $dateRange
            ]);
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            Log::warning('Failed to get analytics data from API: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get balance/finance data from Backend API
     */
    private function getBalanceData($token)
    {
        try {
            $response = $this->api->withToken($token)->get('balance/dashboard');
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            Log::warning('Failed to get balance data from API: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Merge all data sources for the view
     */
    private function mergeData($dashboardData, $analyticsData, $balanceData, $dateRange)
    {
        return [
            // Overview tab data
            'stats' => $dashboardData['stats'] ?? [],
            'recent_activities' => $dashboardData['recent_activities'] ?? [],
            'top_events' => $dashboardData['top_events'] ?? [],

            // Revenue tab data
            'balance' => $balanceData['balance'] ?? [],
            'balance_statistics' => $balanceData['statistics'] ?? [],
            'revenue_analytics' => $analyticsData['revenueAnalytics'] ?? [],
            'total_revenue' => $analyticsData['stats']['total_revenue'] ?? 0,

            // Performance tab data
            'avg_rating' => $analyticsData['stats']['avg_rating'] ?? 0,
            'avg_attendance' => $analyticsData['stats']['avg_attendance'] ?? 0,
            'conversion_rate' => $analyticsData['stats']['conversion_rate'] ?? 0,

            // Growth tab data
            'monthly_trends' => $analyticsData['monthlyTrends'] ?? [],
            'category_analytics' => $analyticsData['categoryAnalytics'] ?? [],
            'user_analytics' => $analyticsData['userAnalytics'] ?? [],
            'event_analytics' => $analyticsData['eventAnalytics'] ?? [],

            // Common data
            'date_range' => $dateRange,
            'date_range_info' => $analyticsData['date_range'] ?? [],
        ];
    }

    /**
     * Get empty/default data for error cases
     */
    private function getEmptyData($errorMessage = null)
    {
        return [
            'stats' => [
                'total_events' => 0,
                'active_events' => 0,
                'total_participants' => 0,
                'this_month_events' => 0,
            ],
            'recent_activities' => [],
            'top_events' => [],
            'balance' => [
                'total_earned' => 0,
                'available_balance' => 0,
                'pending_withdrawal' => 0,
                'platform_fee_total' => 0,
            ],
            'balance_statistics' => [],
            'revenue_analytics' => [],
            'total_revenue' => 0,
            'avg_rating' => 0,
            'avg_attendance' => 0,
            'conversion_rate' => 0,
            'monthly_trends' => [],
            'category_analytics' => [],
            'user_analytics' => [],
            'event_analytics' => [],
            'date_range' => '30',
            'date_range_info' => [],
            'error' => $errorMessage ?? 'Tidak dapat memuat data dashboard',
        ];
    }

    /**
     * Show admin profile/certificate settings page
     */
    public function profile(Request $request)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
            }

            // Get current user profile from backend
            $response = $this->api->withToken($token)->get('profile');

            if (!$response || !isset($response['success']) || !$response['success']) {
                return back()->with('error', 'Gagal mengambil data profil');
            }

            $user = $response['data'];

            return view('admin.profile.index', compact('user'));

        } catch (\Exception $e) {
            Log::error('Admin profile error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Upload logo via backend API
     */
    public function uploadLogo(Request $request)
    {
        try {
            $request->validate([
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ], 401);
            }

            // Send file to backend API using postMultipart
            $response = $this->api->postMultipart('profile/logo', [
                'logo' => $request->file('logo')
            ], $token);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Logo upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload signature via backend API
     */
    public function uploadSignature(Request $request)
    {
        try {
            $request->validate([
                'signature' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ], 401);
            }

            // Send file to backend API using postMultipart
            $response = $this->api->postMultipart('profile/signature', [
                'signature' => $request->file('signature')
            ], $token);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Signature upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete logo
     */
    public function deleteLogo(Request $request)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ], 401);
            }

            $response = $this->api->withToken($token)->delete('profile/logo');

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Delete logo error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete signature
     */
    public function deleteSignature(Request $request)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ], 401);
            }

            $response = $this->api->withToken($token)->delete('profile/signature');

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Delete signature error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show certificate preview with sample data
     */
    public function certificatePreview(Request $request)
    {
        try {
            $token = Session::get('api_token');

            if (!$token) {
                return redirect()->route('login');
            }

            // Get current user profile from backend
            $response = $this->api->withToken($token)->get('profile');

            if (!$response || !isset($response['success']) || !$response['success']) {
                return response('Failed to load preview', 500);
            }

            $user = $response['data'];

            // Create sample data for preview
            $sampleData = (object)[
                'participant_name' => 'John Doe',
                'event_title' => 'Sample Event Title',
                'event_date' => now()->format('d F Y'),
                'organizer_name' => $user['name'] ?? 'Organizer Name',
                'organizer_logo' => $user['logo_url'] ?? null,
                'organizer_signature' => $user['signature_url'] ?? null,
            ];

            return view('admin.profile.certificate-preview', compact('sampleData'));

        } catch (\Exception $e) {
            Log::error('Certificate preview error: ' . $e->getMessage());
            return response('Failed to load preview', 500);
        }
    }
}