<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Barangay;
use App\Models\Municipality;
use App\Models\User;
use App\Policies\ActivityLogPolicy;
use App\Policies\BarangayPolicy;
use App\Policies\MunicipalityPolicy;
use App\Policies\UserPolicy;
use App\Observers\ActivityLogObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Colors\Color;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Gate::policy(Municipality::class, MunicipalityPolicy::class);
        Gate::policy(Barangay::class, BarangayPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);

        Event::subscribe(\App\Listeners\LogAuthenticationActivity::class);
        Event::listen(\Illuminate\Auth\Events\Login::class, \App\Listeners\CacheTotalEntitiesOnLogin::class);

        ActivityLog::observe(ActivityLogObserver::class);

        Barangay::observe(\App\Observers\BarangayObserver::class);
        Municipality::observe(\App\Observers\MunicipalityObserver::class);

        FilamentColor::register([
            'primary' => Color::hex('#1A508E'),
            'danger' => Color::hex('#AF1E21'),
            'warning' => Color::hex('#F4AD1D'),
        ]);
    }
}
