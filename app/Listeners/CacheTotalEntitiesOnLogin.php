<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;
use App\Models\Barangay;
use App\Models\Municipality;

class CacheTotalEntitiesOnLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Cache totals for performance in dashboard widgets (like Active Forms)
        Cache::rememberForever('total_barangays', function () {
            return Barangay::count();
        });

        Cache::rememberForever('total_municipalities', function () {
            return Municipality::count();
        });

        Cache::rememberForever('all_barangays_list', function () {
            return Barangay::select('id', 'name', 'municipality_id')->with('municipality:id,name')->get();
        });

        Cache::rememberForever('all_municipalities_list', function () {
            return Municipality::select('id', 'name')->get();
        });
    }
}
