<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Event;
use App\Models\EventParticipant;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Notification types
     */
    const TYPE_ATTENDANCE_REMINDER = 'attendance_reminder';
    const TYPE_PAYMENT_CONFIRMATION = 'payment_confirmation';
    const TYPE_BOOKMARK_EVENT = 'bookmark_event';
    const TYPE_EVENT_UPDATE = 'event_update';
    const TYPE_EVENT_CANCELLED = 'event_cancelled';
    const TYPE_EVENT_REMINDER = 'event_reminder';
    const TYPE_REGISTRATION_SUCCESS = 'registration_success';

    /**
     * Create attendance reminder notification
     */
    public function sendAttendanceReminder(User $user, Event $event): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'type' => self::TYPE_ATTENDANCE_REMINDER,
            'title' => 'Pengingat Kehadiran Event',
            'message' => "Jangan lupa untuk hadir di event '{$event->title}' yang akan dimulai pada " . 
                         $event->start_date->format('d M Y H:i') . ". Lokasi: {$event->location}",
            'is_read' => false,
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'start_date' => $event->start_date->toIso8601String(),
                'location' => $event->location,
            ]
        ]);
    }

    /**
     * Send payment confirmation notification
     */
    public function sendPaymentConfirmation(User $user, Event $event, EventParticipant $participant): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'type' => self::TYPE_PAYMENT_CONFIRMATION,
            'title' => 'Konfirmasi Pembayaran Event',
            'message' => "Pembayaran Anda untuk event '{$event->title}' sebesar Rp " . 
                         number_format($participant->amount_paid, 0, ',', '.') . 
                         " telah berhasil dikonfirmasi. Invoice: {$participant->payment_reference}",
            'is_read' => false,
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'amount_paid' => $participant->amount_paid,
                'payment_reference' => $participant->payment_reference,
                'paid_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Send bookmark event notification
     */
    public function sendBookmarkNotification(User $user, Event $event, array $categories = []): Notification
    {
        $categoryText = !empty($categories) 
            ? ' ke kategori: ' . implode(', ', $categories)
            : '';

        return Notification::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'type' => self::TYPE_BOOKMARK_EVENT,
            'title' => 'Event Berhasil Disimpan',
            'message' => "Event '{$event->title}' telah berhasil ditambahkan ke bookmark Anda{$categoryText}. " .
                         "Event akan dimulai pada " . $event->start_date->format('d M Y H:i'),
            'is_read' => false,
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'categories' => $categories,
                'bookmarked_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Send registration success notification
     */
    public function sendRegistrationSuccess(User $user, Event $event): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'type' => self::TYPE_REGISTRATION_SUCCESS,
            'title' => 'Pendaftaran Event Berhasil',
            'message' => "Anda berhasil terdaftar untuk event '{$event->title}'. " .
                         "Event akan dimulai pada " . $event->start_date->format('d M Y H:i'),
            'is_read' => false,
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'start_date' => $event->start_date->toIso8601String(),
            ]
        ]);
    }

    /**
     * Send event update notification to all participants
     */
    public function notifyEventUpdate(Event $event, string $updateMessage): void
    {
        $participants = EventParticipant::where('event_id', $event->id)
            ->with('user')
            ->get();

        foreach ($participants as $participant) {
            Notification::create([
                'user_id' => $participant->user_id,
                'event_id' => $event->id,
                'type' => self::TYPE_EVENT_UPDATE,
                'title' => 'Update Event',
                'message' => "Event '{$event->title}' telah diperbarui. {$updateMessage}",
                'is_read' => false,
                'data' => [
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'update_message' => $updateMessage,
                ]
            ]);
        }
    }

    /**
     * Send event cancellation notification to all participants
     */
    public function notifyEventCancellation(Event $event, string $reason = ''): void
    {
        $participants = EventParticipant::where('event_id', $event->id)
            ->with('user')
            ->get();

        $reasonText = $reason ? " Alasan: {$reason}" : '';

        foreach ($participants as $participant) {
            Notification::create([
                'user_id' => $participant->user_id,
                'event_id' => $event->id,
                'type' => self::TYPE_EVENT_CANCELLED,
                'title' => 'Event Dibatalkan',
                'message' => "Mohon maaf, event '{$event->title}' telah dibatalkan.{$reasonText}",
                'is_read' => false,
                'data' => [
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'reason' => $reason,
                    'cancelled_at' => now()->toIso8601String(),
                ]
            ]);
        }
    }

    /**
     * Send event reminder notification (before event starts)
     */
    public function sendEventReminder(User $user, Event $event, int $hoursBefore): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'type' => self::TYPE_EVENT_REMINDER,
            'title' => 'Pengingat Event',
            'message' => "Event '{$event->title}' akan dimulai dalam {$hoursBefore} jam. " .
                         "Waktu: " . $event->start_date->format('d M Y H:i') . ". Lokasi: {$event->location}",
            'is_read' => false,
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'start_date' => $event->start_date->toIso8601String(),
                'hours_before' => $hoursBefore,
                'location' => $event->location,
            ]
        ]);
    }

    /**
     * Get notification statistics for user
     */
    public function getUserNotificationStats(User $user): array
    {
        $total = Notification::where('user_id', $user->id)->count();
        $unread = Notification::where('user_id', $user->id)->unread()->count();
        $read = Notification::where('user_id', $user->id)->read()->count();

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $read,
        ];
    }

    /**
     * Send attendance reminders for upcoming events
     * This should be called by a scheduled job
     */
    public function sendUpcomingEventReminders(int $hoursBefore = 24): int
    {
        $targetTime = now()->addHours($hoursBefore);
        
        // Get events starting within the specified timeframe
        $upcomingEvents = Event::where('start_date', '>=', now())
            ->where('start_date', '<=', $targetTime)
            ->where('status', 'active')
            ->get();

        $sentCount = 0;

        foreach ($upcomingEvents as $event) {
            // Get all registered participants who haven't attended yet
            $participants = EventParticipant::where('event_id', $event->id)
                ->whereNull('attended_at')
                ->with('user')
                ->get();

            foreach ($participants as $participant) {
                // Check if reminder already sent
                $alreadySent = Notification::where('user_id', $participant->user_id)
                    ->where('event_id', $event->id)
                    ->where('type', self::TYPE_ATTENDANCE_REMINDER)
                    ->where('created_at', '>=', now()->subDay())
                    ->exists();

                if (!$alreadySent) {
                    $this->sendAttendanceReminder($participant->user, $event);
                    $sentCount++;
                }
            }
        }

        return $sentCount;
    }
}