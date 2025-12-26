<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\DebugController;
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
use App\Http\Controllers\EventParticipationController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    // Forgot & Reset Password
    Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
// Handle reset password (server-side simplified)

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Participant Dashboard
Route::middleware(['api.auth', 'api.role:participant'])->group(function () {
    Route::get('/participant/dashboard', [ParticipantDashboardController::class, 'index'])->name('participant.dashboard');
    Route::get('/participant/profile', [ParticipantDashboardController::class, 'profile'])->name('participant.profile');
    Route::put('/participant/profile', [ParticipantDashboardController::class, 'updateProfile'])->name('participant.profile.update');
    Route::post('/participant/profile/change-password', [ParticipantDashboardController::class, 'changePassword'])->name('participant.profile.change-password');
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

    // Feedback
    Route::prefix('participant/feedback')->name('participant.feedback.')->group(function () {
        Route::get('/create/{event}', [FeedbackController::class, 'create'])->name('create');
        Route::post('/store/{event}', [FeedbackController::class, 'store'])->name('store');
        Route::get('/certificate/{event}/download', [FeedbackController::class, 'downloadCertificate'])->name('certificate.download');
    });
});

// Public Event Search & Browse (Available to all users)
Route::get('/', [ParticipantEventController::class, 'home'])->name('home');
Route::get('/events', [ParticipantEventController::class, 'index'])->name('events.index');
Route::get('/events/explore', [ParticipantEventController::class, 'index'])->name('events.explore');
Route::get('/events/{event}', [ParticipantEventController::class, 'show'])->name('events.show');

// Event Participation (Requires Auth)
Route::middleware(['api.auth'])->group(function () {
    Route::post('/events/{event}/register', [EventParticipationController::class, 'register'])->name('events.register');
    Route::get('/my-participations', [EventParticipationController::class, 'myParticipations'])->name('my.participations');
    Route::get('/participations/{participation}/qr-code', [EventParticipationController::class, 'showQrCode'])->name('participations.qr-code');
    Route::delete('/participations/{participation}', [EventParticipationController::class, 'cancel'])->name('participations.cancel');
    Route::post('/events/{event}/attendance', [EventParticipationController::class, 'checkAttendance'])->name('events.check-attendance');

    // Payment creation
    Route::post('/events/{event}/payment', [PaymentController::class, 'createPayment'])->name('events.payment.create');
});

// Payment pages
Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');
Route::get('/payments/failure', [PaymentController::class, 'failure'])->name('payments.failure');
Route::get('/payments/status/{participant}', [PaymentController::class, 'status'])->name('payments.status');

        // Admin Dashboard Routes (Organizer-specific data)
        Route::middleware(['api.auth', 'api.role:admin'])->prefix('admin')->group(function () {
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
    Route::get('/events/{event}/feedbacks', [EventController::class, 'feedbacks'])->name('admin.events.feedbacks');
    Route::post('/events/{event}/generate-summary', [EventController::class, 'generateSummary'])->name('admin.events.generate-summary');
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
    
    // Analytics (redirects to dashboard with specific tab)
    Route::get('/analytics', function() {
        return redirect()->route('admin.dashboard', ['tab' => 'growth']);
    })->name('admin.analytics');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('admin.analytics.export');
    Route::get('/analytics/realtime', [AnalyticsController::class, 'realtime'])->name('admin.analytics.realtime');

    // Finance & Withdrawals
    Route::get('/finance', [FinanceController::class, 'index'])->name('admin.finance.index');
    Route::post('/finance/withdrawals/request', [FinanceController::class, 'withdrawalRequest'])->name('admin.finance.withdrawals.request');
    Route::get('/events/{event}/finance', [FinanceController::class, 'show'])->name('admin.events.finance');

    Route::prefix('notifications')->name('admin.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Profile & Certificate Template Management
    Route::get('/profile', [AdminDashboardController::class, 'profile'])->name('admin.profile');
    Route::post('/profile/logo', [AdminDashboardController::class, 'uploadLogo'])->name('admin.profile.logo');
    Route::post('/profile/signature', [AdminDashboardController::class, 'uploadSignature'])->name('admin.profile.signature');
    Route::delete('/profile/logo', [AdminDashboardController::class, 'deleteLogo'])->name('admin.profile.logo.delete');
    Route::delete('/profile/signature', [AdminDashboardController::class, 'deleteSignature'])->name('admin.profile.signature.delete');
    Route::get('/profile/certificate-preview', [AdminDashboardController::class, 'certificatePreview'])->name('admin.profile.preview');

});

// Super Admin Routes (Platform-wide management)
Route::middleware(['api.auth', 'api.role:super_admin'])->prefix('super-admin')->name('super.admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    
    // Organizers Management
    Route::get('/organizers', [SuperAdminController::class, 'organizers'])->name('organizers');
    Route::get('/organizers/{id}', [SuperAdminController::class, 'organizerDetail'])->name('organizer.detail');
    Route::post('/organizers/{id}/toggle', [SuperAdminController::class, 'toggleOrganizerStatus'])->name('organizer.toggle');
    
    // Events Management
    Route::get('/events', [SuperAdminController::class, 'events'])->name('events');
    Route::get('/events/{id}', [SuperAdminController::class, 'eventDetail'])->name('event.detail');
    
    // Categories Management
    Route::get('/categories', [SuperAdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [SuperAdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{id}', [SuperAdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [SuperAdminController::class, 'destroyCategory'])->name('categories.destroy');
    
    // Users Management
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
    Route::post('/users/{id}/suspend', [SuperAdminController::class, 'suspendUser'])->name('users.suspend');
    Route::post('/users/{id}/activate', [SuperAdminController::class, 'activateUser'])->name('users.activate');
    Route::delete('/users/{id}', [SuperAdminController::class, 'deleteUser'])->name('users.delete');

    // Withdrawal Management
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [WithdrawalController::class, 'index'])->name('index');
        Route::get('/{id}', [WithdrawalController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [WithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [WithdrawalController::class, 'reject'])->name('reject');
    });

    // Debug endpoint
    Route::get('/debug/api', [DebugController::class, 'testSuperAdminApi'])->name('debug.api');
});
