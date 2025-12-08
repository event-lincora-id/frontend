<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ParticipantDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ParticipantEventController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BookmarkController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

// Home page - redirect to home route
Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/api-docs', function () {
    return view('api-docs');
});

Route::get('/guide', function () {
    return view('guide');
})->name('guide');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/auth/create-session', [AuthController::class, 'createSessionFromToken'])->name('auth.create-session');
    Route::get('/forgot-password', function () {return view('auth.forgot-password');})->name('password.request');
    Route::get('/reset-password/{token}', function ($token) {return view('auth.reset-password', ['token' => $token]);})->name('password.reset');
    // Handle forgot password (server-side simplified)
Route::post('/forgot-password', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $email = $request->input('email');

    // Generate token and store in cache for 60 minutes
    $token = Str::random(60);
    $cacheKey = 'password_reset_' . $email;
    Cache::put($cacheKey, $token, now()->addHour());

    $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($email));

    return back()->with([
        'reset_token' => $token,
        'reset_url' => $resetUrl,
        'message' => 'Token reset berhasil dibuat. Salin token atau klik link untuk melanjutkan.'
    ]);
})->name('password.email');

// Handle reset password (server-side simplified)
Route::post('/reset-password', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'token' => 'required',
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $email = $request->input('email');
    $token = $request->input('token');
    $cacheKey = 'password_reset_' . $email;
    $cachedToken = Cache::get($cacheKey);

    if (!$cachedToken || $cachedToken !== $token) {
        return back()->withErrors(['token' => 'Token tidak valid atau sudah kedaluwarsa. Silakan buat token baru.'])->withInput();
    }

    // Update user password
    $user = User::where('email', $email)->first();
    $user->password = Hash::make($request->input('password'));
    $user->save();

    // Remove token from cache
    Cache::forget($cacheKey);

    return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
})->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Participant Dashboard
Route::middleware(['auth', 'role:participant'])->group(function () {
    Route::get('/participant/dashboard', [ParticipantDashboardController::class, 'index'])->name('participant.dashboard');
    Route::get('/participant/profile', [ParticipantDashboardController::class, 'profile'])->name('participant.profile');
    Route::put('/participant/profile', [ParticipantDashboardController::class, 'updateProfile'])->name('participant.profile.update');
    Route::get('/attendance/scanner', [AttendanceController::class, 'scanner'])->name('attendance.scanner');
    Route::post('/attendance/mark', [AttendanceController::class, 'markAttendance'])->name('attendance.mark');

    // Notifications
    Route::prefix('participant/notifications')->name('participant.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Bookmarks
    Route::prefix('participant/bookmarks')->name('participant.bookmarks.')->group(function () {
    Route::get('/', [BookmarkController::class, 'index'])->name('index');
    Route::post('/{event}/toggle', [BookmarkController::class, 'toggle'])->name('toggle');
    Route::post('/{event}', [BookmarkController::class, 'store'])->name('store');
    Route::put('/{bookmark}', [BookmarkController::class, 'update'])->name('update');
    Route::delete('/{event}', [BookmarkController::class, 'destroy'])->name('destroy');
    Route::get('/{event}/check', [BookmarkController::class, 'check'])->name('check');
    });
});

// Public Event Search & Browse (Available to all users)
Route::get('/', [ParticipantEventController::class, 'home'])->name('home');
Route::get('/events', [ParticipantEventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [ParticipantEventController::class, 'show'])->name('events.show');

// Payment pages
Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');
Route::get('/payments/failure', [PaymentController::class, 'failure'])->name('payments.failure');
Route::get('/payments/status/{participant}', [PaymentController::class, 'status'])->name('payments.status');

        // Admin Dashboard Routes (Organizer-specific data)
        Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Users Management
    Route::resource('users', UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy'
    ]);
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    
    // Events Management
    Route::resource('events', EventController::class)->names([
        'index' => 'admin.events.index',
        'create' => 'admin.events.create',
        'store' => 'admin.events.store',
        'show' => 'admin.events.show',
        'edit' => 'admin.events.edit',
        'update' => 'admin.events.update',
        'destroy' => 'admin.events.destroy'
    ]);
    Route::post('/events/{event}/toggle-status', [EventController::class, 'toggleStatus'])->name('admin.events.toggle-status');
    Route::get('/events/{event}/participants', [EventController::class, 'participants'])->name('admin.events.participants');
    Route::get('/events/{event}/qr-code', [AttendanceController::class, 'showQRCode'])->name('attendance.qr-code');
    Route::get('/events/{event}/attendance/participants', [AttendanceController::class, 'getParticipants'])->name('attendance.participants');
    
    // Categories Management
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit'])->names([
        'index' => 'admin.categories.index',
        'store' => 'admin.categories.store',
        'update' => 'admin.categories.update',
        'destroy' => 'admin.categories.destroy'
    ]);
    Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('admin.categories.toggle-status');
    Route::get('/categories/{category}/statistics', [CategoryController::class, 'statistics'])->name('admin.categories.statistics');
    
    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('admin.analytics.export');
    Route::get('/analytics/realtime', [AnalyticsController::class, 'realtime'])->name('admin.analytics.realtime');

    // Finance
    Route::get('/finance', [FinanceController::class, 'index'])->name('admin.finance.index');
    Route::get('/events/{event}/finance', [FinanceController::class, 'show'])->name('admin.events.finance');

    Route::prefix('notifications')->name('admin.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

});
