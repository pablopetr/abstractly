<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearSourceCache extends Command
{
    protected $signature = 'source:clear-cache';

    protected $description = 'Clear all cached source fetch results';

    public function handle(): int
    {
        Cache::flush();

        $this->info('Source fetch cache cleared.');

        return self::SUCCESS;
    }
}
