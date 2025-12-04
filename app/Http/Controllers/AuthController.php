<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->isSuperAdmin() || $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('participant.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials'
        ])->withInput();
    }

    /**
     * Handle register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,participant',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        User::create([
            'name' => $request->full_name,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')
            ->with('success', 'Registration successful! Please login to continue.');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
    
/**
 * Send password reset link (Simplified - tanpa email)
 */
public function forgotPassword(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Generate random token
        $token = Str::random(60);
        
        // Store token in cache with 1 hour expiration
        $cacheKey = 'password_reset_' . $request->email;
        cache()->put($cacheKey, [
            'token' => $token,
            'email' => $request->email,
            'created_at' => now()
        ], now()->addHour());

        return response()->json([
            'success' => true,
            'message' => 'Password reset token generated',
            'data' => [
                'token' => $token, // Untuk testing, tampilkan token
                'reset_url' => url("/reset-password/{$token}?email=" . urlencode($request->email))
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Unable to generate reset token: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Reset password (Simplified)
 */
public function resetPassword(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'token' => 'required',
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Verify token from cache
        $cacheKey = 'password_reset_' . $request->email;
        $cachedData = cache()->get($cacheKey);

        if (!$cachedData || $cachedData['token'] !== $request->token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token'
            ], 422);
        }

        // Update password
        $user = User::where('email', $request->email)->firstOrFail();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Clear cache
        cache()->forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Unable to reset password: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Create session from API token (compatibility endpoint)
     */
    public function createSessionFromToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Session created successfully',
            'redirect_url' => $user->isSuperAdmin() || $user->isAdmin()
                ? route('admin.dashboard')
                : route('participant.dashboard'),
        ]);
    }
}
