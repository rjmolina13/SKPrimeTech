<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Http;

class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        if ($event->user) {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'login',
                'description' => 'User logged in',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    public function handleFailedLogin(Failed $event): void
    {
        $url = env('CLOUDFLARE_WORKER_LOG_URL');

        if (! $url) {
            return;
        }

        try {
            $email = $event->credentials['email'] ?? 'Unknown Email';
            $userIdentifier = $event->user ? $event->user->name : 'Unregistered User';

            Http::timeout(3)->post($url, [
                'type' => 'login_attempt',
                'app_name' => config('app.name', 'SKPrime'),
                'env' => config('app.env', 'local'),
                'data' => [
                    'user' => $userIdentifier,
                    'email' => $email,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'logout',
                'description' => 'User logged out',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Failed::class => 'handleFailedLogin',
            Logout::class => 'handleLogout',
        ];
    }
}
