<?php

/**
 * Test Notification System
 * 
 * Jalankan dengan: php test_notifications.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Category;
use App\Services\NotificationService;
use App\Models\Notification;

echo "=== TESTING NOTIFICATION SYSTEM ===\n\n";

// 1. Check if NotificationService exists
echo "1. Checking NotificationService...\n";
try {
    $notificationService = app(NotificationService::class);
    echo "   ✓ NotificationService loaded successfully\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 2. Get a test user
echo "2. Getting test user...\n";
$user = User::where('role', 'participant')->first();
if (!$user) {
    echo "   ✗ No participant user found. Please create a user first.\n\n";
    exit(1);
}
echo "   ✓ Found user: {$user->name} (ID: {$user->id})\n\n";

// 3. Get or create a test event
echo "3. Getting test event...\n";
$event = Event::where('status', 'published')->where('is_active', true)->first();
if (!$event) {
    echo "   ! No published event found. Creating test event...\n";
    
    // Get or create category
    $category = Category::first();
    if (!$category) {
        $category = Category::create([
            'name' => 'Test Category',
            'description' => 'Category for testing',
            'is_active' => true,
        ]);
    }
    
    // Get admin/organizer user
    $organizer = User::where('role', 'admin')->first();
    if (!$organizer) {
        $organizer = User::first();
    }
    
    // Create test event
    try {
        $event = Event::create([
            'user_id' => $organizer->id,
            'category_id' => $category->id,
            'title' => 'Test Event - Notification System',
            'description' => 'Event created for testing notification system',
            'location' => 'Test Location',
            'event_type' => 'online',
            'contact_info' => 'test@example.com',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(2),
            'is_paid' => false,
            'price' => 0,
            'quota' => 100,
            'registered_count' => 0,
            'status' => 'published',  // Valid values: draft, published, cancelled, completed
            'is_active' => true,
        ]);
        
        echo "   ✓ Test event created successfully\n";
    } catch (Exception $e) {
        echo "   ✗ Failed to create event: " . $e->getMessage() . "\n\n";
        exit(1);
    }
}
echo "   ✓ Found event: {$event->title} (ID: {$event->id})\n";
echo "     Status: {$event->status} | Active: " . ($event->is_active ? 'Yes' : 'No') . "\n";
echo "     Start date: {$event->start_date->format('Y-m-d H:i:s')}\n\n";

// 4. Test creating notifications
echo "4. Testing notification creation...\n";

try {
    // Test 1: Registration Success Notification
    echo "   - Creating registration success notification...\n";
    $notification1 = $notificationService->sendRegistrationSuccess($user, $event);
    echo "     ✓ Created: {$notification1->title}\n";

    // Test 2: Bookmark Notification
    echo "   - Creating bookmark notification...\n";
    $notification2 = $notificationService->sendBookmarkNotification($user, $event, ['Upcoming', 'Saved']);
    echo "     ✓ Created: {$notification2->title}\n";

    // Test 3: Event Reminder
    echo "   - Creating event reminder notification...\n";
    $notification3 = $notificationService->sendEventReminder($user, $event, 24);
    echo "     ✓ Created: {$notification3->title}\n";

    // Test 4: Attendance Reminder
    echo "   - Creating attendance reminder notification...\n";
    $notification4 = $notificationService->sendAttendanceReminder($user, $event);
    echo "     ✓ Created: {$notification4->title}\n";

    echo "\n   ✓ All notifications created successfully!\n\n";
} catch (Exception $e) {
    echo "   ✗ Error creating notifications: " . $e->getMessage() . "\n";
    echo "     Stack trace: " . $e->getTraceAsString() . "\n\n";
    exit(1);
}

// 5. Test retrieving notifications
echo "5. Testing notification retrieval...\n";
$allNotifications = Notification::where('user_id', $user->id)->get();
echo "   Total notifications for user: " . $allNotifications->count() . "\n";

$unreadNotifications = Notification::where('user_id', $user->id)->unread()->get();
echo "   Unread notifications: " . $unreadNotifications->count() . "\n\n";

// 6. Test notification stats
echo "6. Testing notification statistics...\n";
try {
    $stats = $notificationService->getUserNotificationStats($user);
    echo "   Total: {$stats['total']}\n";
    echo "   Unread: {$stats['unread']}\n";
    echo "   Read: {$stats['read']}\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// 7. Display last 5 notifications
echo "7. Displaying last 5 notifications...\n";
$recentNotifications = Notification::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

foreach ($recentNotifications as $notif) {
    $status = $notif->is_read ? '✓ Read' : '○ Unread';
    echo "   [{$status}] {$notif->type}\n";
    echo "       Title: {$notif->title}\n";
    echo "       Message: " . substr($notif->message, 0, 80) . "...\n";
    echo "       Created: {$notif->created_at->format('Y-m-d H:i:s')}\n\n";
}

// 8. Test mark as read
echo "8. Testing mark as read functionality...\n";
if ($recentNotifications->count() > 0) {
    $firstNotif = $recentNotifications->first();
    if (!$firstNotif->is_read) {
        $firstNotif->update(['is_read' => true]);
        echo "   ✓ Marked notification #{$firstNotif->id} as read\n\n";
    } else {
        echo "   ℹ First notification already read\n\n";
    }
}

echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";
echo "\nNotifications in database:\n";
$finalUnread = Notification::where('user_id', $user->id)->unread()->count();
$finalRead = Notification::where('user_id', $user->id)->read()->count();
echo "- Total: {$stats['total']}\n";
echo "- Unread: {$finalUnread}\n";
echo "- Read: {$finalRead}\n";

echo "\n✅ You can now test the routes:\n";
echo "\nWeb Routes (via Browser after login):\n";
echo "- GET    http://localhost/event-connect/participant/notifications\n";
echo "- GET    http://localhost/event-connect/participant/notifications/unread-count\n";
echo "- POST   http://localhost/event-connect/participant/notifications/mark-all-read\n";
echo "- POST   http://localhost/event-connect/participant/notifications/{id}/read\n";
echo "- DELETE http://localhost/event-connect/participant/notifications/{id}\n\n";

echo "API Routes (requires Bearer token):\n";
echo "- GET    http://localhost/event-connect/api/notifications\n";
echo "- GET    http://localhost/event-connect/api/notifications/unread-count\n";
echo "- POST   http://localhost/event-connect/api/notifications/mark-all-read\n";
echo "- POST   http://localhost/event-connect/api/notifications/{id}/read\n";
echo "- DELETE http://localhost/event-connect/api/notifications/{id}\n\n";