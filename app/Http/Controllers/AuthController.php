<?php

namespace App\Http\Controllers;

use App\Services\BackendApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Show register form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    try {
        Log::info('Attempting login to API', [
            'email' => $credentials['email'],
            'api_url' => config('services.backend.base_url')
        ]);

        // Call API login
        $response = $this->api->post('auth/login', [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]);

        Log::info('API Response received', ['response' => $response]);

        if (!isset($response['success']) || !$response['success']) {
            return back()->withErrors([
                'email' => $response['message'] ?? 'Login failed'
            ])->withInput();
        }

        // Store token and user data in session
        $token = $response['data']['token'] ?? null;
        $user = $response['data']['user'] ?? null;

        if (!$token || !$user) {
            return back()->withErrors([
                'email' => 'Invalid response from server'
            ])->withInput();
        }

        Session::put('api_token', $token);
        Session::put('user', $user);

        // Redirect based on role
        $role = $user['role'] ?? 'participant';
        
        if ($role === 'super_admin') {
            return redirect()->route('super.admin.dashboard');
        }
        
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('participant.dashboard');

    } catch (\Exception $e) {
        Log::error('Login error: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->withErrors([
            'email' => 'Tidak dapat terhubung ke server. ' . $e->getMessage()
        ])->withInput();
    }
}

    /**
     * Handle register
     */
    public function register(Request $request)
{
    $validated = $request->validate([
        'full_name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255',
        'password' => 'required|string|min:8|confirmed',
        'role' => 'required|in:admin,participant',
    ]);

    try {
        Log::info('Attempting registration', [
            'email' => $validated['email'],
            'role' => $validated['role']
        ]);

        // Call API register
        $response = $this->api->post('auth/register', [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'password_confirmation' => $request->password_confirmation,
            'role' => $validated['role'],
        ]);

        Log::info('API register response', ['response' => $response]);

        if (!isset($response['success']) || !$response['success']) {
            $errorMessage = $response['message'] ?? 'Registration failed';
            
            // Handle specific validation errors from API
            if (isset($response['errors'])) {
                return back()->withErrors($response['errors'])->withInput();
            }
            
            return back()->withErrors([
                'email' => $errorMessage
            ])->withInput();
        }

        return redirect()->route('login')
            ->with('success', 'Registration successful! Please login to continue.');

    } catch (\Exception $e) {
        Log::error('Register error: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->withErrors([
            'email' => 'Tidak dapat terhubung ke server. ' . $e->getMessage()
        ])->withInput();
    }
}

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        try {
            $token = Session::get('api_token');
            if ($token) {
                $this->api->withToken($token)->post('auth/logout');
            }
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
        }

        Session::forget('api_token');
        Session::forget('user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
 * Show forgot password form
 */
public function showForgotPassword()
{
    return view('auth.forgot-password');
}

/**
 * Handle forgot password request
 */
public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    try {
        Log::info('Forgot password request', ['email' => $request->email]);

        // Call API forgot password
        $response = $this->api->post('auth/forgot-password', [
            'email' => $request->email,
        ]);

        Log::info('Forgot password response', ['response' => $response]);

        if ($response['success'] ?? false) {
            return back()->with([
                'message' => $response['message'] ?? 'Reset password link telah dikirim ke email Anda',
                'reset_token' => $response['data']['token'] ?? null,
                'reset_url' => $response['data']['reset_url'] ?? null,
            ]);
        }

        return back()->withErrors([
            'email' => $response['message'] ?? 'Email tidak ditemukan'
        ])->withInput();

    } catch (\Exception $e) {
        Log::error('Forgot password error: ' . $e->getMessage());
        return back()->withErrors([
            'email' => 'Tidak dapat terhubung ke server. Silakan coba lagi.'
        ])->withInput();
    }
}

/**
 * Show reset password form
 */
public function showResetPassword(Request $request, $token)
{
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->email,
    ]);
}

/**
 * Handle reset password
 */
public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    try {
        Log::info('Reset password request', ['email' => $request->email]);

        // Call API reset password
        $response = $this->api->post('auth/reset-password', [
            'token' => $request->token,
            'email' => $request->email,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ]);

        Log::info('Reset password response', ['response' => $response]);

        if ($response['success'] ?? false) {
            return redirect()->route('login')
                ->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
        }

        return back()->withErrors([
            'email' => $response['message'] ?? 'Reset password gagal'
        ])->withInput();

    } catch (\Exception $e) {
        Log::error('Reset password error: ' . $e->getMessage());
        return back()->withErrors([
            'email' => 'Tidak dapat terhubung ke server. Silakan coba lagi.'
        ])->withInput();
    }
}
}