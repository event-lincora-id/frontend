<?php
// test_bookmarks.php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Event;
use App\Models\EventBookmark;

echo "=== TESTING BOOKMARK FEATURE ===\n\n";

$user = User::where('role', 'participant')->first();
$event = Event::where('status', 'published')->first();

if (!$user || !$event) {
    echo "❌ User atau Event tidak ditemukan\n";
    exit(1);
}

echo "User: {$user->full_name}\n";
echo "Event: {$event->title}\n\n";

// Test 1: Create Bookmark
echo "1. Testing create bookmark...\n";
$bookmark = EventBookmark::create([
    'user_id' => $user->id,
    'event_id' => $event->id,
    'category' => 'saved',
    'notes' => 'Interesting event!'
]);
echo "   ✓ Bookmark created (ID: {$bookmark->id})\n\n";

// Test 2: Check if bookmarked
echo "2. Testing hasBookmarked method...\n";
$isBookmarked = $user->hasBookmarked($event->id);
echo "   " . ($isBookmarked ? '✓' : '✗') . " Event is bookmarked: " . ($isBookmarked ? 'YES' : 'NO') . "\n\n";

// Test 3: Get all bookmarks
echo "3. Testing get all bookmarks...\n";
$bookmarks = $user->bookmarks()->with('event')->get();
echo "   Total bookmarks: {$bookmarks->count()}\n";
foreach ($bookmarks as $bm) {
    echo "   - {$bm->event->title} (Category: {$bm->category})\n";
}

echo "\n✅ Test completed!\n";
echo "Access bookmarks at: http://localhost/event-connect/participant/bookmarks\n";