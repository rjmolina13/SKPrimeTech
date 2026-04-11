<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Http;

class ActivityLogObserver
{
    /**
     * Handle the ActivityLog "created" event.
     */
    public function created(ActivityLog $activityLog): void
    {
        $url = env('CLOUDFLARE_WORKER_LOG_URL');

        if (! $url) {
            return;
        }

        try {
            // Send the activity log to the Cloudflare worker
            Http::timeout(3)->post($url, [
                'type' => 'activity_log', // Tells the worker this is an activity log
                'app_name' => config('app.name', 'SKPrime'),
                'env' => config('app.env', 'local'),
                'data' => [
                    'user' => $activityLog->user ? $activityLog->user->name : 'System/Guest',
                    'action' => $activityLog->action,
                    'description' => $activityLog->description,
                    'ip_address' => $activityLog->ip_address,
                    'created_at' => $activityLog->created_at->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            // Silently fail to prevent app crash if worker is down
        }
    }
}
