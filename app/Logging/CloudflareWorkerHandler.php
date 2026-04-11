<?php

namespace App\Logging;

use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class CloudflareWorkerHandler extends AbstractProcessingHandler
{
    /**
     * Writes the record down to the log of the implementing handler
     */
    protected function write(LogRecord $record): void
    {
        $url = env('CLOUDFLARE_WORKER_LOG_URL');
        
        if (! $url) {
            return;
        }

        try {
            // Send a fire-and-forget HTTP request to Cloudflare with a short timeout
            Http::timeout(3)->post($url, [
                'level' => $record->level->getName(),
                'message' => $record->message,
                'context' => $record->context,
                'env' => config('app.env', 'local'),
                'app_name' => config('app.name', 'SKPrime'),
            ]);
        } catch (\Exception $e) {
            // Silently fail if the worker is unreachable to prevent application crashes
            // from logging errors.
        }
    }
}
