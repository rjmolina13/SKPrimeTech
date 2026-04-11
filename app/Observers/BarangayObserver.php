<?php

namespace App\Observers;

use App\Models\Barangay;
use Illuminate\Support\Facades\Cache;

class BarangayObserver
{
    /**
     * Handle the Barangay "created" event.
     */
    public function created(Barangay $barangay): void
    {
        Cache::forget('total_barangays');
        Cache::forget('all_barangays_list');
    }

    /**
     * Handle the Barangay "deleted" event.
     */
    public function deleted(Barangay $barangay): void
    {
        Cache::forget('total_barangays');
        Cache::forget('all_barangays_list');
    }
}
