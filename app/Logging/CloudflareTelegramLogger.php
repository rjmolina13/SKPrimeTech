<?php

namespace App\Logging;

use Monolog\Level;
use Monolog\Logger;

class CloudflareTelegramLogger
{
    /**
     * Create a custom Monolog instance.
     */
    public function __invoke(array $config): Logger
    {
        // Default to logging Error level and above if not specified in config
        $level = $config['level'] ?? Level::Error;
        
        return new Logger('cloudflare_telegram', [
            new CloudflareWorkerHandler($level)
        ]);
    }
}
