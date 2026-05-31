<?php

namespace App\Console\Commands;

use App\Models\BusinessSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearBusinessSettingsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'business-settings:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all business settings cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all business settings keys
        $keys = BusinessSetting::pluck('key');

        // Clear cache for each key
        foreach ($keys as $key) {
            Cache::forget(BusinessSetting::CACHE_PREFIX . $key);
        }

        // Clear Laravel config cache
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('view:clear');

        $this->info('Business settings cache cleared successfully!');

        return 0;
    }
}
