<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Services\BackendApiService;

class DebugController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Test super admin endpoints to see what data is returned
     */
    public function testSuperAdminApi()
    {
        $token = Session::get('api_token');
        $user = Session::get('user');

        if (!$token || !$user) {
            return response()->json([
                'error' => 'Not authenticated',
                'token' => $token ? 'exists' : 'missing',
                'user' => $user ? 'exists' : 'missing',
                'session_data' => [
                    'api_token' => $token,
                    'user_role' => $user['role'] ?? 'unknown'
                ]
            ], 401);
        }

        $results = [];

        // Test statistics endpoint
        try {
            Log::info('Testing /super-admin/statistics with token: ' . substr($token, 0, 20) . '...');
            $stats = $this->api->withToken($token)->get('super-admin/statistics');
            $results['statistics'] = [
                'status' => 'success',
                'data' => $stats
            ];
        } catch (\Exception $e) {
            Log::error('Statistics endpoint error: ' . $e->getMessage());
            $results['statistics'] = [
                'status' => 'error',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }

        // Test organizers endpoint
        try {
            Log::info('Testing /super-admin/organizers with token');
            $organizers = $this->api->withToken($token)->get('super-admin/organizers', ['page' => 1, 'per_page' => 5]);
            $results['organizers'] = [
                'status' => 'success',
                'data' => $organizers
            ];
        } catch (\Exception $e) {
            Log::error('Organizers endpoint error: ' . $e->getMessage());
            $results['organizers'] = [
                'status' => 'error',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }

        // Test events endpoint
        try {
            Log::info('Testing /super-admin/events with token');
            $events = $this->api->withToken($token)->get('super-admin/events', ['page' => 1, 'per_page' => 5]);
            $results['events'] = [
                'status' => 'success',
                'data' => $events
            ];
        } catch (\Exception $e) {
            Log::error('Events endpoint error: ' . $e->getMessage());
            $results['events'] = [
                'status' => 'error',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }

        Log::info('Debug API Test Results:', $results);

        return response()->json([
            'authentication' => [
                'token_exists' => !!$token,
                'user_exists' => !!$user,
                'user_role' => $user['role'] ?? 'unknown',
                'api_base_url' => config('services.backend.base_url')
            ],
            'results' => $results
        ], 200, ['Content-Type' => 'application/json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
