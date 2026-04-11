<?php

namespace App\Observers;

use App\Models\Municipality;
use Illuminate\Support\Facades\Cache;

class MunicipalityObserver
{
    /**
     * Handle the Municipality "created" event.
     */
    public function created(Municipality $municipality): void
    {
        Cache::forget('total_municipalities');
        Cache::forget('all_municipalities_list');
    }

    /**
     * Handle the Municipality "deleted" event.
     */
    public function deleted(Municipality $municipality): void
    {
        Cache::forget('total_municipalities');
        Cache::forget('all_municipalities_list');
    }
}
