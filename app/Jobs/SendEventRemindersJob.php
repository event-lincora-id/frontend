<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEventRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $hoursBefore;

    /**
     * Create a new job instance.
     */
    public function __construct(int $hoursBefore = 24)
    {
        $this->hoursBefore = $hoursBefore;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $sentCount = $notificationService->sendUpcomingEventReminders($this->hoursBefore);
            
            Log::info("Event reminders sent successfully", [
                'count' => $sentCount,
                'hours_before' => $this->hoursBefore,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send event reminders", [
                'error' => $e->getMessage(),
                'hours_before' => $this->hoursBefore,
            ]);
            
            throw $e;
        }
    }
}