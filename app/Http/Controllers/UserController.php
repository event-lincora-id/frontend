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
     * Display a listing of users
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
            $params = [
                'page' => $request->get('page', 1),
            ];
            
            if ($request->has('search') && $request->search) {
                $params['search'] = $request->search;
            }
            
            if ($request->has('role') && $request->role) {
                $params['role'] = $request->role;
            }

            // Get users from API
            $response = $this->api->withToken($token)->get('admin/users', $params);
            
            $usersData = $response['data']['data'] ?? [];
            $pagination = $response['data']['pagination'] ?? [
                'total' => count($usersData),
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1
            ];

            // Convert to objects
            $usersData = array_map(fn($u) => (object) $u, $usersData);

            // Create Laravel paginator
            $users = new LengthAwarePaginator(
                $usersData,
                $pagination['total'] ?? count($usersData),
                $pagination['per_page'] ?? 10,
                $pagination['current_page'] ?? 1,
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
                'error' => 'Tidak dapat memuat data users: ' . $e->getMessage()
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