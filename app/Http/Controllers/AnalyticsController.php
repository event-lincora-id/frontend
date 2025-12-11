<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackendApiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
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

            if (!$token) {
                return redirect()->route('login');
            }

            $params = [
                'date_range' => $request->get('date_range', '30'),
            ];

            // Try to get analytics from API
            try {
                $response = $this->api->withToken($token)->get('admin/analytics', $params);
                $analytics = $response['data'] ?? [];
            } catch (\Exception $e) {
                // If API endpoint doesn't exist, use fallback data
                Log::warning('Analytics API not available, using fallback data');
                $analytics = [];
            }

            // Prepare data for view with fallback defaults
            $monthlyData = [
                'total_events' => $analytics['stats']['total_events'] ?? 0,
                'total_participants' => $analytics['stats']['total_participants'] ?? 0,
                'active_events' => $analytics['stats']['active_events'] ?? 0,
                'avg_attendance' => $analytics['stats']['avg_attendance'] ?? 0,
                'top_events' => $analytics['top_events'] ?? [],
            ];

            $categoryStats = $analytics['category_stats'] ?? [];
            
            // Get events and categories for fallback calculation
            if (empty($categoryStats)) {
                try {
                    $categoriesResponse = $this->api->get('categories');
                    $categories = $categoriesResponse['data'] ?? [];
                    
                    $categoryStats = array_map(function($cat) {
                        return [
                            'name' => $cat['name'] ?? 'Unknown',
                            'count' => 0,
                            'percentage' => 0,
                            'color' => $cat['color'] ?? '#3B82F6'
                        ];
                    }, $categories);
                } catch (\Exception $e) {
                    $categoryStats = [];
                }
            }

            $chartData = [
                'labels' => $analytics['chart_labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'events' => $analytics['chart_events'] ?? array_fill(0, 12, 0),
                'users' => $analytics['chart_users'] ?? array_fill(0, 12, 0),
            ];

            return view('admin.analytics.index', compact('monthlyData', 'categoryStats', 'chartData'));

        } catch (\Exception $e) {
            Log::error('Analytics index error: ' . $e->getMessage());
            
            // Return view with empty data
            $monthlyData = [
                'total_events' => 0,
                'total_participants' => 0,
                'active_events' => 0,
                'avg_attendance' => 0,
                'top_events' => [],
            ];
            
            $categoryStats = [];
            
            $chartData = [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'events' => array_fill(0, 12, 0),
                'users' => array_fill(0, 12, 0),
            ];
            
            return view('admin.analytics.index', [
                'monthlyData' => $monthlyData,
                'categoryStats' => $categoryStats,
                'chartData' => $chartData,
                'error' => 'Tidak dapat memuat data analytics: ' . $e->getMessage()
            ]);
        }
    }

    public function export(Request $request)
    {
        try {
            $token = Session::get('api_token');

            $params = [
                'format' => $request->get('format', 'csv'),
                'date_range' => $request->get('date_range', '30'),
            ];

            $response = $this->api->withToken($token)->get('admin/analytics/export', $params);

            if (isset($response['data']['download_url'])) {
                return redirect($response['data']['download_url']);
            }

            return redirect()->back()
                ->with('error', 'Export gagal');

        } catch (\Exception $e) {
            Log::error('Analytics export error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function realtime(Request $request)
    {
        try {
            $token = Session::get('api_token');
            $response = $this->api->withToken($token)->get('admin/analytics/realtime');
            
            return response()->json($response['data'] ?? []);

        } catch (\Exception $e) {
            Log::error('Analytics realtime error: ' . $e->getMessage());
            return response()->json(['error' => 'Tidak dapat memuat data realtime'], 500);
        }
    }
}